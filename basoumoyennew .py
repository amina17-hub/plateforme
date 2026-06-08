# -*- coding: utf-8 -*-

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.neighbors import KNeighborsRegressor
from sklearn.tree import DecisionTreeClassifier, DecisionTreeRegressor
import itertools
import math
import os
import json
from joblib import dump

try:
    VISUALIZATION_AVAILABLE = True
except ImportError:
    VISUALIZATION_AVAILABLE = False

os.makedirs("models", exist_ok=True)

# ─────────────────────────────────────────────
# 1. CHARGEMENT & PRÉTRAITEMENT
# ─────────────────────────────────────────────
file_name = "skikda_unified_dataset2.csv"

df_full = pd.read_csv(file_name, encoding="utf-8-sig").copy()

text_cols = [
    "client_id",
    "client_name",
    "artisan_name",
    "service_type",
    "description",
    "city",
    "commune",
]
for col in text_cols:
    if col in df_full.columns:
        df_full[col] = (
            df_full[col]
            .fillna("")
            .astype(str)
            .str.normalize("NFKC")
            .str.strip()
        )

numeric_cols = ["price", "rating", "latitude", "longitude"]
for col in numeric_cols:
    if col in df_full.columns:
        df_full[col] = pd.to_numeric(df_full[col], errors="coerce")

df_full = df_full.dropna(
    subset=["service_type", "commune", "latitude", "longitude"]
).copy()

if "price" in df_full.columns:
    df_full["price"] = df_full["price"].fillna(
        df_full.groupby("service_type")["price"].transform("median")
    )
    df_full["price"] = df_full["price"].fillna(df_full["price"].median())

if "rating" in df_full.columns:
    df_full["rating"] = df_full["rating"].fillna(
        df_full.groupby("service_type")["rating"].transform("median")
    )
    df_full["rating"] = df_full["rating"].fillna(df_full["rating"].median())

# enlever doublons artisans pour recommendation
df_artisans_unique = df_full.drop_duplicates(subset=["artisan_id"]).copy()

# ─────────────────────────────────────────────
# 2. DATASET POUR ANALYSE CAUSALE
# ─────────────────────────────────────────────
df = df_full.drop(
    columns=[
        "id",
        "client_id",
        "client_name",
        "artisan_id",
        "artisan_name",
        "description",
        "city",
        "created_at",
        "updated_at",
        "user_id",
    ],
    errors="ignore",
).copy()

categorical_features = ["service_type", "commune"]
for feature in categorical_features:
    df[feature] = df[feature].astype("category").cat.codes

median_price = df["price"].median()
df["low_medium_price"] = (df["price"] <= median_price).astype(int)

T = "low_medium_price"
target_feature = "rating"
features_to_adjust = ["service_type", "commune"]
df = df[features_to_adjust + [T, target_feature]]

# ─────────────────────────────────────────────
# 3. FONCTIONS CAUSALES
# ─────────────────────────────────────────────
def evaluate(X, y, extra=""):
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )
    model = RandomForestRegressor(max_depth=20, random_state=123)
    model.fit(X_train, y_train)

    y_pred_train = model.predict(X_train)
    print(f"{extra} Train error: {np.mean((y_train - y_pred_train) ** 2):.4f}")

    y_pred_test = model.predict(X_test)
    print(f"{extra} Test error:  {np.mean((y_test - y_pred_test) ** 2):.4f}")


def get_data(df, T):
    curr_df = df.copy()
    T_ = curr_df[T]

    treated = curr_df[curr_df[T] == 1]
    control = curr_df[curr_df[T] == 0]

    X = curr_df.drop([T, target_feature], axis="columns")
    y = curr_df[target_feature]

    X_treated = treated.drop([T, target_feature], axis="columns")
    y_treated = treated[target_feature]

    X_control = control.drop([T, target_feature], axis="columns")
    y_control = control[target_feature]

    return X, y, X_treated, y_treated, X_control, y_control, T_


def clean(e, y, treatment):
    remove = np.where(e == treatment)
    e = np.delete(e, remove)
    y = y.to_numpy()
    y = np.delete(y, remove)
    return e, y

# ─────────────────────────────────────────────
# 4. CALCUL DES ATE
# ─────────────────────────────────────────────
ATE = {}

# Naïf
treated_outcome = np.mean(df[df[T] == 1][target_feature])
control_outcome = np.mean(df[df[T] == 0][target_feature])
ATE["naive"] = round(treated_outcome - control_outcome, 4)
print(f"Naive ATE: {ATE['naive']}")

# IPW Random Forest
X, y, X_treated, y_treated, X_control, y_control, T_ = get_data(df, T)
n = len(X)

rf_classifier = RandomForestClassifier(max_depth=20, random_state=123)
rf_classifier.fit(X, T_)

e_t = rf_classifier.predict_proba(X_treated)[:, 1]
e_c = rf_classifier.predict_proba(X_control)[:, 1]
e_t, y_treated_clean = clean(e_t, y_treated, 0)
e_c, y_control_clean = clean(e_c, y_control, 1)

ATE["ipw"] = round(
    np.sum(y_treated_clean / e_t) / n - np.sum(y_control_clean / (1 - e_c)) / n,
    4,
)
print(f"IPW ATE (Random Forest): {ATE['ipw']}")

# IPW Decision Tree
X, y, X_treated, y_treated, X_control, y_control, T_ = get_data(df, T)
dt_classifier = DecisionTreeClassifier(random_state=123)
dt_classifier.fit(X, T_)

e_t = dt_classifier.predict_proba(X_treated)[:, 1]
e_c = dt_classifier.predict_proba(X_control)[:, 1]
e_t, y_treated_clean = clean(e_t, y_treated, 0)
e_c, y_control_clean = clean(e_c, y_control, 1)

ATE["ipw -> dtc"] = round(
    np.sum(y_treated_clean / e_t) / n - np.sum(y_control_clean / (1 - e_c)) / n,
    4,
)
print(f"IPW ATE (Decision Tree): {ATE['ipw -> dtc']}")

# S-Learner Random Forest
X = df.drop(target_feature, axis=1)
y = df[target_feature]
evaluate(X, y)

s_learner_rf = RandomForestRegressor(max_depth=20, random_state=123)
s_learner_rf.fit(X, y)

X_t = X.copy()
X_t[T] = 1
X_c = X.copy()
X_c[T] = 0

ATE["s-learner"] = round(np.mean(s_learner_rf.predict(X_t) - s_learner_rf.predict(X_c)), 4)
print(f"S-Learner ATE (Random Forest): {ATE['s-learner']}")

# S-Learner Decision Tree
X = df.drop(target_feature, axis=1)
y = df[target_feature]

s_learner_dt = DecisionTreeRegressor(random_state=123)
s_learner_dt.fit(X, y)

X_t = X.copy()
X_t[T] = 1
X_c = X.copy()
X_c[T] = 0

ATE["s-learner -> dtc"] = round(np.mean(s_learner_dt.predict(X_t) - s_learner_dt.predict(X_c)), 4)
print(f"S-Learner ATE (Decision Tree): {ATE['s-learner -> dtc']}")

# T-Learner Random Forest
treated = df[df[T] == 1]
control = df[df[T] == 0]

X_1 = treated.drop(target_feature, axis=1)
y_1 = treated[target_feature]
X_0 = control.drop(target_feature, axis=1)
y_0 = control[target_feature]

evaluate(X_1, y_1, extra="X_1")
evaluate(X_0, y_0, extra="X_0")

t_learner_rf_treated = RandomForestRegressor(max_depth=20, random_state=123)
t_learner_rf_control = RandomForestRegressor(max_depth=20, random_state=123)
t_learner_rf_treated.fit(X_1, y_1)
t_learner_rf_control.fit(X_0, y_0)

X_all = df.drop(columns=[target_feature])

ATE["t-learner"] = round(
    np.mean(t_learner_rf_treated.predict(X_all) - t_learner_rf_control.predict(X_all)),
    4,
)
print(f"T-Learner ATE (Random Forest): {ATE['t-learner']}")

# T-Learner Decision Tree
t_learner_dt_treated = DecisionTreeRegressor(random_state=123)
t_learner_dt_control = DecisionTreeRegressor(random_state=123)
t_learner_dt_treated.fit(X_1, y_1)
t_learner_dt_control.fit(X_0, y_0)

ATE["t-learner -> dtc"] = round(
    np.mean(t_learner_dt_treated.predict(X_all) - t_learner_dt_control.predict(X_all)),
    4,
)
print(f"T-Learner ATE (Decision Tree): {ATE['t-learner -> dtc']}")

# Matching
X, y, X_treated, y_treated, X_control, y_control, T_ = get_data(df, T)

knn_regressor = KNeighborsRegressor(1, metric="hamming")
knn_regressor.fit(X_control, y_control)

knn_regressor_2 = KNeighborsRegressor(1, metric="hamming")
knn_regressor_2.fit(X_treated, y_treated)

ITE1 = y_treated - knn_regressor.predict(X_treated)
ITE2 = knn_regressor_2.predict(X_control) - y_control
ATE["matching"] = round(np.mean(pd.concat([ITE1, ITE2])), 4)
print(f"Matching ATE: {ATE['matching']}")

# Backdoor Adjustment
X_sub = df.copy()[features_to_adjust + [T, target_feature]]
values = [list(X_sub[f].unique()) for f in features_to_adjust]
size = len(X_sub)
probs = {}

for combo in itertools.product(*values):
    mask = (
        (X_sub[features_to_adjust[0]] == combo[0])
        & (X_sub[features_to_adjust[1]] == combo[1])
    )
    combo_appearances = X_sub[mask]
    probs[combo] = {"x": len(combo_appearances) / size}

    if combo_appearances.empty:
        continue

    tr = combo_appearances[combo_appearances[T] == 1]
    ct = combo_appearances[combo_appearances[T] == 0]
    p_tr = len(tr) / len(combo_appearances)
    probs[combo]["T/x"] = [1 - p_tr, p_tr]

    y_tr = tr[tr[target_feature] > tr[target_feature].median()] if not tr.empty else pd.DataFrame()
    y_ct = ct[ct[target_feature] > ct[target_feature].median()] if not ct.empty else pd.DataFrame()

    probs[combo]["Y/T,x"] = {
        0: [0, 0] if ct.empty else [1 - len(y_ct) / len(ct), len(y_ct) / len(ct)],
        1: [0, 0] if tr.empty else [1 - len(y_tr) / len(tr), len(y_tr) / len(tr)],
    }

treated_sum = sum(
    probs[c]["Y/T,x"][1][1] * probs[c]["x"]
    for c in probs
    if probs[c]["x"] != 0 and "Y/T,x" in probs[c]
)
control_sum = sum(
    probs[c]["Y/T,x"][0][1] * probs[c]["x"]
    for c in probs
    if probs[c]["x"] != 0 and "Y/T,x" in probs[c]
)

ATE["backdoor_adj"] = round(treated_sum - control_sum, 4)
print(f"Backdoor Adjustment ATE: {ATE['backdoor_adj']}")

# ATE consensus
robust_methods = ["ipw", "s-learner", "t-learner", "matching", "backdoor_adj"]
CONSENSUS_ATE = round(np.mean([ATE[m] for m in robust_methods]), 4)
print(f"\nATE Consensus : {CONSENSUS_ATE}")

# ─────────────────────────────────────────────
# 5. SAUVEGARDE DES RÉSULTATS ET MODÈLES EN PKL
# ─────────────────────────────────────────────
dump(ATE, "models/ate_results.pkl")
dump(CONSENSUS_ATE, "models/consensus_ate.pkl")
dump(rf_classifier, "models/random_forest_classifier.pkl")
dump(s_learner_rf, "models/s_learner_rf.pkl")
dump(t_learner_rf_treated, "models/t_learner_rf_treated.pkl")
dump(t_learner_rf_control, "models/t_learner_rf_control.pkl")
print("Résultats et modèles sauvegardés dans 'models/' en .pkl")

# ─────────────────────────────────────────────
# 6. VISUALISATION
# ─────────────────────────────────────────────
if VISUALIZATION_AVAILABLE:
    ate_keys = list(ATE.keys())
    ate_values = [ATE[k] for k in ate_keys]

    plt.figure(figsize=(12, 6))
    sns.barplot(x=ate_keys, y=ate_values, hue=ate_keys, palette="viridis", legend=False)
    plt.axhline(
        y=CONSENSUS_ATE,
        color="red",
        linestyle="--",
        linewidth=1.5,
        label=f"Consensus ATE = {CONSENSUS_ATE}"
    )
    plt.axhline(y=0, color="gray", linestyle="-", linewidth=0.8)
    plt.title("Comparaison des ATE par méthode")
    plt.xlabel("Méthode")
    plt.ylabel("ATE")
    plt.xticks(rotation=45, ha="right")
    plt.legend()
    plt.tight_layout()
    plt.savefig("ate_visualization.png")
    plt.close()
    print("Visualisation sauvegardée dans 'ate_visualization.png'")

# ─────────────────────────────────────────────
# 7. RECOMMANDATION
# ─────────────────────────────────────────────
def haversine_distance(lat1, lon1, lat2, lon2):
    R = 6371
    dlat = math.radians(lat2 - lat1)
    dlon = math.radians(lon2 - lon1)
    a = (
        math.sin(dlat / 2) ** 2
        + math.cos(math.radians(lat1)) * math.cos(math.radians(lat2)) * math.sin(dlon / 2) ** 2
    )
    return R * 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))

def causal_score(artisan_row, consensus_ate, source_df, max_distance_km=50):
    rating_score = artisan_row["rating"] / 5.0

    median_price_full = source_df["price"].median()
    is_low_price = 1 if artisan_row["price"] <= median_price_full else 0
    causal_bonus = is_low_price * max(consensus_ate, 0)

    max_price = source_df["price"].max()
    price_score = 1 - (artisan_row["price"] / max_price)

    distance_score = max(0.0, 1 - (artisan_row["distance_km"] / max_distance_km))

    return (
        0.35 * rating_score
        + 0.20 * price_score
        + 0.25 * distance_score
        + 0.20 * causal_bonus
    )

def find_best_artisan(service_type, client_lat, client_lon, max_distance_km=50, top_n=3):
    artisans = df_artisans_unique[df_artisans_unique["service_type"] == service_type].copy()
    if artisans.empty:
        return []

    artisans["distance_km"] = artisans.apply(
        lambda row: haversine_distance(client_lat, client_lon, row["latitude"], row["longitude"]),
        axis=1
    )

    in_radius = artisans[artisans["distance_km"] <= max_distance_km].copy()

    if not in_radius.empty:
        artisans = in_radius
    else:
        artisans = artisans.nsmallest(min(top_n * 5, len(artisans)), "distance_km").copy()

    artisans["causal_score"] = artisans.apply(
        lambda row: causal_score(
            row,
            consensus_ate=CONSENSUS_ATE,
            source_df=df_full,
            max_distance_km=max_distance_km
        ),
        axis=1
    )

    artisans = artisans.sort_values(
        by=["causal_score", "rating", "distance_km"],
        ascending=[False, False, True]
    ).head(top_n)

    results = []
    median_price_full = df_full["price"].median()

    for _, row in artisans.iterrows():
        results.append({
            "artisan_id": row["artisan_id"],
            "artisan_name": row["artisan_name"],
            "service_type": row["service_type"],
            "commune": row["commune"],
            "latitude": row["latitude"],
            "longitude": row["longitude"],
            "price": float(row["price"]),
            "rating": float(row["rating"]),
            "distance_km": round(float(row["distance_km"]), 2),
            "causal_score": round(float(row["causal_score"]), 4),
            "price_category": "Bas/Moyen" if row["price"] <= median_price_full else "Élevé"
        })

    return results

# ─────────────────────────────────────────────
# 8. SERVICES COMPLÉMENTAIRES (LOGIQUE MANUELLE)
# ─────────────────────────────────────────────
causal_logic = [
    ["Maçon", "Plâtrier / Staffeur", "Carreleur", "Peintre", "Électricien", "Plombier"],
    ["Chauffagiste", "Climatisation / Frigoriste", "Réparation Électroménager"],
    ["Menuisier", "Ébéniste", "Décorateur d'intérieur", "Tapissier / Couturier (Linge de Maison)"],
    ["Étanchéiste", "Charpentier", "Serrurier", "Vitrailler / Miroitier"],
    ["Organisation d'événements", "Traiteur / Pâtissier", "Coiffure à Domicile", "Esthéticienne à Domicile"],
    ["Jardinier / Paysagiste", "Piscines (Constructeur/Maintenance)", "Dératiseur / Désinsectiseur", "Nettoyage professionnel (Spécialisé)"],
    ["Soutien Scolaire", "Fabrication de parfums", "Fabrication de Bougies", "Cosmétiques (Crèmes hydratantes)"],
    ["Installateur Systèmes Sécurité", "Antenniste", "Serrurier"],
    ["Aide-Ménagère Spécialisée", "Restauration de vêtements", "Nettoyage professionnel (Spécialisé)"]
]

def recommend_complementary_services(current_service, client_lat, client_lon, max_distance_km=50):
    recommendations = []
    seen_services = set()

    for workflow in causal_logic:
        if current_service in workflow:
            idx = workflow.index(current_service)
            next_services = workflow[idx + 1:]

            for service in next_services:
                if service in seen_services:
                    continue

                best = find_best_artisan(
                    service_type=service,
                    client_lat=client_lat,
                    client_lon=client_lon,
                    max_distance_km=max_distance_km,
                    top_n=1
                )

                if best:
                    entry = best[0]
                    entry["recommended_because"] = f"Étape naturelle après '{current_service}'"
                    recommendations.append(entry)
                    seen_services.add(service)
            break

    if not recommendations:
        fallback_services = [
            "Nettoyage professionnel (Spécialisé)",
            "Serrurier",
            "Électricien"
        ]
        for service in fallback_services:
            if service == current_service or service in seen_services:
                continue

            best = find_best_artisan(
                service_type=service,
                client_lat=client_lat,
                client_lon=client_lon,
                max_distance_km=max_distance_km,
                top_n=1
            )

            if best:
                entry = best[0]
                entry["recommended_because"] = "Service populaire (recommandation générale)"
                recommendations.append(entry)
                seen_services.add(service)

    return recommendations

# ─────────────────────────────────────────────
# 9. RECOMMANDATION COMPLÈTE
# ─────────────────────────────────────────────
def full_recommendation(service_type, client_lat, client_lon, max_distance_km=50):
    print(f"\n{'═' * 60}")
    print(f"Demande          : {service_type}")
    print(f"Position client  : ({client_lat}, {client_lon})")
    print(f"Distance max     : {max_distance_km} km")
    print(f"ATE consensus    : {CONSENSUS_ATE}")
    print(f"{'═' * 60}")

    best_artisans = find_best_artisan(
        service_type=service_type,
        client_lat=client_lat,
        client_lon=client_lon,
        max_distance_km=max_distance_km,
        top_n=3
    )

    complementary = recommend_complementary_services(
        current_service=service_type,
        client_lat=client_lat,
        client_lon=client_lon,
        max_distance_km=max_distance_km
    )

    result = {
        "service_demande": service_type,
        "ate_consensus": CONSENSUS_ATE,
        "meilleurs_artisans": best_artisans,
        "services_complementaires": complementary
    }

    print(f"\nTop artisans pour '{service_type}' :")
    if not best_artisans:
        print("  Aucun artisan trouvé.")
    else:
        for i, a in enumerate(best_artisans, 1):
            print(
                f"  {i}. {a['artisan_name']} ({a['commune']}) | "
                f"Rating: {a['rating']} | "
                f"Prix: {a['price']} DZD | "
                f"Distance: {a['distance_km']} km | "
                f"Score: {a['causal_score']} | "
                f"{a['price_category']}"
            )

    if complementary:
        print("\nServices complémentaires recommandés :")
        for c in complementary:
            print(
                f"  → {c['service_type']} : {c['artisan_name']} ({c['commune']}) | "
                f"Distance: {c['distance_km']} km | "
                f"Score: {c['causal_score']}"
            )
            print(f"     Raison : {c['recommended_because']}")

    return result

# ─────────────────────────────────────────────
# 10. EXEMPLE D'UTILISATION
# ─────────────────────────────────────────────
if __name__ == "__main__":
    result = full_recommendation(
        service_type="Maçon",
        client_lat=36.876,
        client_lon=6.906,
        max_distance_km=30
    )

    with open("recommendation_result.json", "w", encoding="utf-8") as f:
        json.dump(result, f, ensure_ascii=False, indent=2)

    print("\nRésultat exporté dans 'recommendation_result.json'")