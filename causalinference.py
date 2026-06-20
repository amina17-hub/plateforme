
# -*- coding: utf-8 -*-
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.neighbors import KNeighborsRegressor
from sklearn.tree import DecisionTreeClassifier, DecisionTreeRegressor
from xgboost import XGBClassifier, XGBRegressor
import itertools
import os
from joblib import dump

# Vérifier si l'import de visualisation existe
try:
    VISUALIZATION_AVAILABLE = True
except ImportError:
    VISUALIZATION_AVAILABLE = False

# Création des dossiers requis
os.makedirs("models", exist_ok=True)
os.makedirs("plots", exist_ok=True)

# ─────────────────────────────────────────────
# 1. CHARGEMENT & PRÉTRAITEMENT
# ─────────────────────────────────────────────
file_name = "storage/skikda_unified_dataset2.csv"
df_full = pd.read_csv(file_name, encoding="utf-8-sig").copy()

# Nettoyage texte
text_cols = ["client_id", "client_name", "artisan_name", "service_type", "description", "city", "commune"]
for col in text_cols:
    if col in df_full.columns:
        df_full[col] = df_full[col].fillna("").astype(str).str.normalize("NFKC").str.strip()

# Nettoyage numérique
numeric_cols = ["price", "rating", "latitude", "longitude"]
for col in numeric_cols:
    if col in df_full.columns:
        df_full[col] = pd.to_numeric(df_full[col], errors="coerce")

# Supprimer lignes invalides
df_full = df_full.dropna(subset=["service_type", "commune", "latitude", "longitude"]).copy()

# Remplissage valeurs manquantes
if "price" in df_full.columns:
    df_full["price"] = df_full["price"].fillna(df_full.groupby("service_type")["price"].transform("median"))
    df_full["price"] = df_full["price"].fillna(df_full["price"].median())

if "rating" in df_full.columns:
    df_full["rating"] = df_full["rating"].fillna(
        df_full.groupby("service_type")["rating"].transform("median")
    )
    df_full["rating"] = df_full["rating"].fillna(df_full["rating"].median())

# Dataset pour entraînement causal
df = df_full.drop(
    columns=[
        "id", "client_id", "client_name", "artisan_id", "artisan_name",
        "description", "city", "created_at", "updated_at", "user_id",
        "latitude", "longitude",
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
# 2. FONCTIONS UTILITAIRES
# ─────────────────────────────────────────────
def evaluate(X, y, extra=""):
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
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
# 3. CALCUL DES ATE
# ─────────────────────────────────────────────
# Dictionnaire global pour tous les calculs d'ATE
ATE = {}

# --- Naïf ---
treated_outcome = np.mean(df[df[T] == 1][target_feature])
control_outcome = np.mean(df[df[T] == 0][target_feature])
ATE["naive"] = round(treated_outcome - control_outcome, 4)
print(f"Naive ATE: {ATE['naive']}")

X, y, X_treated, y_treated, X_control, y_control, T_ = get_data(df, T)
n = len(X)

# --- IPW Random Forest ---
rf_classifier = RandomForestClassifier(max_depth=40, random_state=123)
rf_classifier.fit(X, T_)
e_t = rf_classifier.predict_proba(X_treated)[:, 1]
e_c = rf_classifier.predict_proba(X_control)[:, 1]
e_t_clean, y_treated_clean = clean(e_t, y_treated, 0)
e_c_clean, y_control_clean = clean(e_c, y_control, 1)
ATE["ipw -> rf"] = round(np.sum(y_treated_clean / e_t_clean) / n - np.sum(y_control_clean / (1 - e_c_clean)) / n, 4)

# --- IPW Decision Tree ---
dt_classifier = DecisionTreeClassifier(random_state=123)
dt_classifier.fit(X, T_)
e_t = dt_classifier.predict_proba(X_treated)[:, 1]
e_c = dt_classifier.predict_proba(X_control)[:, 1]
e_t_clean, y_treated_clean = clean(e_t, y_treated, 0)
e_c_clean, y_control_clean = clean(e_c, y_control, 1)
ATE["ipw -> dtc"] = round(np.sum(y_treated_clean / e_t_clean) / n - np.sum(y_control_clean / (1 - e_c_clean)) / n, 4)
print(f"IPW ATE (Decision Tree): {ATE['ipw -> dtc']}")

# --- IPW XGBoost ---
xgb_classifier = XGBClassifier(max_depth=10, random_state=123)
xgb_classifier.fit(X, T_)
e_t = xgb_classifier.predict_proba(X_treated)[:, 1]
e_c = xgb_classifier.predict_proba(X_control)[:, 1]
e_t_clean, y_treated_clean = clean(e_t, y_treated, 0)
e_c_clean, y_control_clean = clean(e_c, y_control, 1)
ATE["ipw -> xgb"] = round(np.sum(y_treated_clean / e_t_clean) / n - np.sum(y_control_clean / (1 - e_c_clean)) / n, 4)
print(f"IPW ATE (xgboost): {ATE['ipw -> xgb']}")

# --- S-Learner Random Forest ---
X_s = df.drop(target_feature, axis=1)
y_s = df[target_feature]
evaluate(X_s, y_s)

s_learner_rf = RandomForestRegressor(max_depth=40, random_state=123)
s_learner_rf.fit(X_s, y_s)
X_t = X_s.copy()
X_t[T] = 1
X_c = X_s.copy()
X_c[T] = 0
ATE["s-learner -> rf"] = round(np.mean(s_learner_rf.predict(X_t) - s_learner_rf.predict(X_c)), 4)
print(f"S-Learner ATE (RF): {ATE['s-learner -> rf']}")

# --- S-Learner Decision Tree ---
s_learner_dt = DecisionTreeRegressor(random_state=123)
s_learner_dt.fit(X_s, y_s)
ATE["s-learner -> dtr"] = round(np.mean(s_learner_dt.predict(X_t) - s_learner_dt.predict(X_c)), 4)
print(f"S-Learner ATE (DT): {ATE['s-learner -> dtr']}")

# --- S-Learner XGBoost ---
evaluate(X_s, y_s)
s_learner_xgb = XGBRegressor(max_depth=10, random_state=123)  # Variable renommée pour harmonisation
s_learner_xgb.fit(X_s, y_s)
ATE["s-learner -> xgb"] = round(np.mean(s_learner_xgb.predict(X_t) - s_learner_xgb.predict(X_c)), 4)
print(f"S-Learner ATE (XGB): {ATE['s-learner -> xgb']}")

# --- Préparation T-Learner ---
treated = df[df[T] == 1]
control = df[df[T] == 0]
X_1 = treated.drop(target_feature, axis=1)
y_1 = treated[target_feature]
X_0 = control.drop(target_feature, axis=1)
y_0 = control[target_feature]
X_all = df.drop(columns=[target_feature])

evaluate(X_1, y_1, extra="X_1")
evaluate(X_0, y_0, extra="X_0")

# --- T-Learner XGBoost ---
t_learner_xgbR_treated = XGBRegressor(max_depth=10, random_state=123)
t_learner_xgbR_control = XGBRegressor(max_depth=10, random_state=123)
t_learner_xgbR_treated.fit(X_1, y_1)
t_learner_xgbR_control.fit(X_0, y_0)
ATE["t-learner -> xgb"] = round(np.mean(t_learner_xgbR_treated.predict(X_all) - t_learner_xgbR_control.predict(X_all)), 4)
print(f"T-Learner ATE (XGB): {ATE['t-learner -> xgb']}")

# --- T-Learner Decision Tree ---
t_learner_dt_treated = DecisionTreeRegressor(random_state=123)
t_learner_dt_control = DecisionTreeRegressor(random_state=123)
t_learner_dt_treated.fit(X_1, y_1)
t_learner_dt_control.fit(X_0, y_0)
ATE["t-learner -> dtr"] = round(np.mean(t_learner_dt_treated.predict(X_all) - t_learner_dt_control.predict(X_all)), 4)
print(f"T-Learner ATE (DT): {ATE['t-learner -> dtr']}")

# --- T-Learner Random Forest ---
t_learner_rf_treated = RandomForestRegressor(max_depth=40, random_state=123)
t_learner_rf_control = RandomForestRegressor(max_depth=40, random_state=123)
t_learner_rf_treated.fit(X_1, y_1)
t_learner_rf_control.fit(X_0, y_0)
ATE["t-learner -> rf"] = round(np.mean(t_learner_rf_treated.predict(X_all) - t_learner_rf_control.predict(X_all)), 4)
print(f"T-Learner ATE (Random Forest): {ATE['t-learner -> rf']}")

# --- Matching ---
knn_regressor = KNeighborsRegressor(1, metric="hamming")
knn_regressor.fit(X_control, y_control)
knn_regressor_2 = KNeighborsRegressor(1, metric="hamming")
knn_regressor_2.fit(X_treated, y_treated)
ITE1 = y_treated - knn_regressor.predict(X_treated)
ITE2 = knn_regressor_2.predict(X_control) - y_control
ATE["matching"] = round(np.mean(pd.concat([ITE1, ITE2])), 4)
print(f"Matching ATE: {ATE['matching']}")

# --- Backdoor Adjustment ---
X_sub = df.copy()[features_to_adjust + [T, target_feature]]
values = [list(X_sub[f].unique()) for f in features_to_adjust]
size = len(X_sub)
probs = {}

for combo in itertools.product(*values):
    mask = (X_sub[features_to_adjust[0]] == combo[0]) & (X_sub[features_to_adjust[1]] == combo[1])
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

treated_sum = sum(probs[c]["Y/T,x"][1][1] * probs[c]["x"] for c in probs if probs[c]["x"] != 0 and "Y/T,x" in probs[c])
control_sum = sum(probs[c]["Y/T,x"][0][1] * probs[c]["x"] for c in probs if probs[c]["x"] != 0 and "Y/T,x" in probs[c])
ATE["backdoor_adj"] = round(treated_sum - control_sum, 4)
print(f"Backdoor Adjustment ATE: {ATE['backdoor_adj']}")


# ─────────────────────────────────────────────
# 4. SENSITIVITY ANALYSIS & CONSENSUS (Correction des sous-dictionnaires)
# ─────────────────────────────────────────────
ipw_models = {"IPW-RF": ATE["ipw -> rf"], "IPW-DT": ATE["ipw -> dtc"], "IPW-XGB": ATE["ipw -> xgb"]}
s_models = {"S-RF": ATE["s-learner -> rf"], "S-DT": ATE["s-learner -> dtr"], "S-XGB": ATE["s-learner -> xgb"]}
t_models = {"T-RF": ATE["t-learner -> rf"], "T-DT": ATE["t-learner -> dtr"], "T-XGB": ATE["t-learner -> xgb"]}

sensitivity_results = []
ipw_choices = [()] + [(k,) for k in ipw_models]
s_choices = [()] + [(k,) for k in s_models]
t_choices = [()] + [(k,) for k in t_models]
matching_choices = [(), ("Matching",)]
backdoor_choices = [(), ("Backdoor",)]

# Remplacement de product() par itertools.product()
for ipw_c, s_c, t_c, m_c, b_c in itertools.product(
    ipw_choices, s_choices, t_choices, matching_choices, backdoor_choices
):
    combo = list(ipw_c + s_c + t_c + m_c + b_c)
    if len(combo) < 2:
        continue

    values = []
    for method in combo:
        if method in ipw_models: values.append(ipw_models[method])
        elif method in s_models: values.append(s_models[method])
        elif method in t_models: values.append(t_models[method])
        elif method == "Matching": values.append(ATE["matching"])
        elif method == "Backdoor": values.append(ATE["backdoor_adj"])

    consensus = round(sum(values) / len(values), 4)
    sensitivity_results.append({"Combinaison": " + ".join(combo), "Consensus_ATE": consensus})

df_sensitivity = pd.DataFrame(sensitivity_results).sort_values(by="Consensus_ATE", ascending=False)
print("Nombre total de combinaisons :", len(df_sensitivity))

df_sensitivity.to_csv("plots/ate_sensitivity.csv", index=False, encoding="utf-8-sig")
print("Fichier enregistré : plots/ate_sensitivity.csv")

# Calcul de l'ATE consensus final basé sur XGBoost
robust_methods = ["ipw -> xgb", "s-learner -> xgb"]
CONSENSUS_ATE = round(np.mean([ATE[m] for m in robust_methods]), 4)
print("Méthodes robustes :", robust_methods)
print("ATE Consensus :", CONSENSUS_ATE)


# ─────────────────────────────────────────────
# 5. SAUVEGARDE DES RÉSULTATS ET MODÈLES (.PKL)
# ─────────────────────────────────────────────
print("\n[INFO] Début de la sauvegarde des fichiers .pkl...")
dump(ATE, "models/ate_results.pkl")
dump(CONSENSUS_ATE, "models/consensus_ate.pkl")
dump(xgb_classifier, "models/xgb_classifier.pkl")
dump(s_learner_xgb, "models/s_learner_xgb.pkl")
dump(t_learner_xgbR_treated, "models/t_learner_xgb_treated.pkl")
dump(t_learner_xgbR_control, "models/t_learner_xgb_control.pkl")
print("=> Tous les modèles et résultats ont été sauvegardés avec succès dans 'models/'")


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
    plt.savefig("plots/ate_visualization.png")
    plt.close()
    print("Visualisation sauvegardée dans 'plots/ate_visualization.png'")

