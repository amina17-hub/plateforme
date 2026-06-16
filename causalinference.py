import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.neighbors import KNeighborsRegressor
from sklearn.tree import DecisionTreeClassifier, DecisionTreeRegressor
import itertools
import os
from joblib import dump
#vérifier si l'import de visualisation existe
try:
    VISUALIZATION_AVAILABLE = True
except ImportError:
    VISUALIZATION_AVAILABLE = False
#creation du dossier model si n'existe pas
os.makedirs("models", exist_ok=True)

# ─────────────────────────────────────────────
# 1. CHARGEMENT & PRÉTRAITEMENT
# ─────────────────────────────────────────────
file_name = "storage/skikda_unified_dataset2.csv"
# Dataset complet pour recommandations
df_full = pd.read_csv(file_name, encoding="utf-8-sig").copy()

# Nettoyage texte
text_cols = [
    "client_id",
    "client_name",
    "artisan_name",
    "service_type",
    "description",
    "city",
    "commune",
]
# Parcourir chaque colonne textuelle
for col in text_cols:
    # Vérifier que la colonne existe dans le DataFrame
    if col in df_full.columns:
        # Nettoyage des données textuelles :
        # - Remplacer les valeurs manquantes par une chaîne vide
        # - Convertir toutes les valeurs en chaînes de caractères
        # - Normaliser les caractères Unicode (NFKC)
        # - Supprimer les espaces inutiles au début et à la fin
        df_full[col] = (
            df_full[col]
            .fillna("")
            .astype(str)
            .str.normalize("NFKC")
            .str.strip()
        )

# Nettoyage numérique
# Conversion des colonnes numériques en type numérique
# Les valeurs non convertibles sont remplacées par NaN
numeric_cols = ["price", "rating", "latitude", "longitude"]
for col in numeric_cols:
    if col in df_full.columns:
        df_full[col] = pd.to_numeric(df_full[col], errors="coerce")

# Supprimer lignes invalides
## Suppression des lignes contenant des valeurs manquantes
# dans les colonnes indispensables à la recommandation
df_full = df_full.dropna(
    subset=["service_type", "commune", "latitude", "longitude"]
).copy()

# Remplissage valeurs manquantes
## Imputation des valeurs manquantes de la colonne 'price'
# Remplacement d'abord par la médiane du même type de service par exp 100 150 160 ndiw 150,
# puis par la médiane globale si nécessaire
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

# Dataset pour entraînement causal
## Création d'un dataset dédié à l'analyse causale
# Suppression des colonnes non pertinentes pour l'entraînement des modèles
# (identifiants, informations textuelles, coordonnées géographiques, dates, etc.)
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
        "latitude",
        "longitude",
    ],
    errors="ignore",
).copy()
# Encodage des variables catégorielles en valeurs numériques
# afin qu'elles puissent être utilisées par les modèles de Machine Learning
categorical_features = ["service_type", "commune"]
for feature in categorical_features:
    df[feature] = df[feature].astype("category").cat.codes
# Calcul de la médiane des prix
median_price = df["price"].median()
df["low_medium_price"] = (df["price"] <= median_price).astype(int)
# Création de la variable de traitement (Treatment)
# 1 : prix faible ou moyen
# 0 : prix élevé
T = "low_medium_price"
target_feature = "rating"
features_to_adjust = ["service_type", "commune"]
# Construction du dataset final contenant :
# - les variables de confusion
# - la variable de traitement
# - la variable résultat
df = df[features_to_adjust + [T, target_feature]]

# ─────────────────────────────────────────────
# 2. FONCTIONS UTILITAIRES
# Fonction d'évaluation d'un modèle Random Forest Regressor
# Sépare les données en ensemble d'entraînement (80%)
# et de test (20%), entraîne le modèle puis calcule
# l'erreur quadratique moyenne (MSE) sur les deux ensembles.
# ─────────────────────────────────────────────
def evaluate(X, y, extra=""):
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42
    )
    ## Initialisation du modèle Random Forest Regressor
    model = RandomForestRegressor(max_depth=20, random_state=123)
     # Entraînement du modèle
    model.fit(X_train, y_train)
# Prédiction sur les données d'entraînement
    y_pred_train = model.predict(X_train)
     # Calcul et affichage de l'erreur d'entraînement (MSE)
    print(f"{extra} Train error: {np.mean((y_train - y_pred_train) ** 2):.4f}")
 # Prédiction sur les données de test
    y_pred_test = model.predict(X_test)
     # Calcul et affichage de l'erreur de test (MSE)
    print(f"{extra} Test error:  {np.mean((y_test - y_pred_test) ** 2):.4f}")

# Fonction de préparation des données pour l'analyse causale
# Sépare le dataset en groupe traité (T=1) et groupe contrôle (T=0)
# puis extrait les variables explicatives (X) et la variable résultat (y).

def get_data(df, T):
     # Copie du DataFrame original
    curr_df = df.copy()

    # Variable de traitement
    T_ = curr_df[T]
 # Séparation des individus traités et non traités
    treated = curr_df[curr_df[T] == 1]
    control = curr_df[curr_df[T] == 0]
 # Variables explicatives et variable résultat pour l'ensemble complet
    X = curr_df.drop([T, target_feature], axis="columns")
    y = curr_df[target_feature]
# Variables explicatives et résultat pour le groupe traité
    X_treated = treated.drop([T, target_feature], axis="columns")
    y_treated = treated[target_feature]
 # Variables explicatives et résultat pour le groupe contrôle
    X_control = control.drop([T, target_feature], axis="columns")
    y_control = control[target_feature]
# Retour des différents jeux de données
    return X, y, X_treated, y_treated, X_control, y_control, T_

# Fonction de nettoyage des observations
# Supprime les individus dont la probabilité estiméee
# est égale à la valeur du traitement afin d'éviter
# des problèmes numériques dans certains estimateurs causaux.
def clean(e, y, treatment):
    # Recherche des indices à supprimer
    remove = np.where(e == treatment)
     # Suppression des probabilités concernées
    e = np.delete(e, remove)
     # Conversion de y en tableau numpy
    y = y.to_numpy()
    # Suppression des observations correspondantes
    y = np.delete(y, remove)
    return e, y


# ─────────────────────────────────────────────
# 3. CALCUL DES ATE
## Dictionnaire pour stocker les ATE calculés
# ─────────────────────────────────────────────
ATE = {}

# Naïf
# Calcul de la moyenne du résultat (rating) pour le groupe traité
treated_outcome = np.mean(df[df[T] == 1][target_feature])
 #Calcul de la moyenne du résultat pour le groupe contrôle
control_outcome = np.mean(df[df[T] == 0][target_feature])
# Estimation naïve de l'effet moyen du traitement
ATE["naive"] = round(treated_outcome - control_outcome, 4)
print(f"Naive ATE: {ATE['naive']}")

# IPW RF
# Préparation des données
X, y, X_treated, y_treated, X_control, y_control, T_ = get_data(df, T)
# Nombre total d'observations
n = len(X)
# Entraînement du modèle Random Forest pour estimer le score de propension
rf_classifier = RandomForestClassifier(max_depth=20, random_state=123)
rf_classifier.fit(X, T_)
# Estimation des probabilités d'appartenir au groupe traité
e_t = rf_classifier.predict_proba(X_treated)[:, 1]
e_c = rf_classifier.predict_proba(X_control)[:, 1]
# Nettoyage des observations problématiques
e_t, y_treated_clean = clean(e_t, y_treated, 0)
e_c, y_control_clean = clean(e_c, y_control, 1)

ATE["ipw"] = round(
    np.sum(y_treated_clean / e_t) / n - np.sum(y_control_clean / (1 - e_c)) / n,
    4,
)
print(f"IPW ATE (Random Forest): {ATE['ipw']}")

# IPW DT
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

# S-Learner RF
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

# S-Learner DT
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

# T-Learner RF
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

# T-Learner DT
t_learner_dt_treated = DecisionTreeRegressor(random_state=123)
t_learner_dt_control = DecisionTreeRegressor(random_state=123)
t_learner_dt_treated.fit(X_1, y_1)
t_learner_dt_control.fit(X_0, y_0)

ATE["t-learner -> dtc"] = round(
    np.mean(t_learner_dt_treated.predict(X_all) - t_learner_dt_control.predict(X_all)),
    4,
)
print(f"T-Learner ATE (Decision Tree): {ATE['t-learner -> dtc']}")

# Matching KNN
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

# ─────────────────────────────────────────────
# 4. CONSENSUS ATE
# ─────────────────────────────────────────────
robust_methods = ["ipw", "s-learner", "t-learner", "matching", "backdoor_adj"]
CONSENSUS_ATE = round(np.mean([ATE[m] for m in robust_methods]), 4)
print(f"\nATE Consensus : {CONSENSUS_ATE}")

# Sauvegarde des résultats causaux
dump(ATE, "models/ate_results.pkl")
dump(CONSENSUS_ATE, "models/consensus_ate.pkl")

# ─────────────────────────────────────────────
# 5. SAUVEGARDE DES MODÈLES
# ─────────────────────────────────────────────
dump(rf_classifier, "models/random_forest_classifier.pkl")
dump(dt_classifier, "models/decision_tree_classifier.pkl")
dump(s_learner_rf, "models/s_learner_rf.pkl")
dump(s_learner_dt, "models/decision_tree_regressor_s_learner.pkl")
dump(t_learner_rf_treated, "models/t_learner_rf_treated.pkl")
dump(t_learner_rf_control, "models/t_learner_rf_control.pkl")
dump(t_learner_dt_control, "models/decision_tree_regressor_t0.pkl")
dump(t_learner_dt_treated, "models/decision_tree_regressor_t1.pkl")
dump(knn_regressor, "models/knn_regressor.pkl")
print("Modèles sauvegardés dans 'models/'.")

# ─────────────────────────────────────────────
# 6. VISUALISATION
# ─────────────────────────────────────────────
if VISUALIZATION_AVAILABLE:
    ate_keys = list(ATE.keys())
    ate_values = [ATE[k] for k in ate_keys]
    plt.figure(figsize=(12, 6))
    sns.barplot(x=ate_keys, y=ate_values, hue=ate_keys, palette="viridis", legend=False)
    plt.axhline(y=CONSENSUS_ATE, color="red", linestyle="--", linewidth=1.5, label=f"Consensus ATE = {CONSENSUS_ATE}")
    plt.axhline(y=0, color="gray", linestyle="-", linewidth=0.8)
    plt.title("Comparaison des ATE par méthode")
    plt.xlabel("Méthode")
    plt.ylabel("ATE")
    plt.xticks(rotation=45, ha="right")
    plt.legend()
    plt.tight_layout()
    plt.savefig("ate_visualization.png")
    plt.close()

# ─────────────────────────────────────────────
# VISUALISATION DU DATASET CAUSAL
# ─────────────────────────────────────────────

# Sauvegarder le dataset causal
df.to_csv(
    "causal_dataset.csv",
    index=False,
    encoding="utf-8-sig"
)

print("\nDataset causal sauvegardé : causal_dataset.csv")

# Informations générales
print("\n========== DATASET CAUSAL ==========")

print("Shape :", df.shape)

print("\nColonnes :")
print(df.columns.tolist())

print("\nPremières lignes :")
print(df.head())

print("\nStatistiques :")
print(df.describe())


# ─────────────────────────────────────────────
# Distribution of Rating (Outcome)
# ─────────────────────────────────────────────

print("\nRating")
print("Min :", df["rating"].min())
print("Max :", df["rating"].max())

plt.figure(figsize=(7,4))

sns.histplot(
    df["rating"],
    kde=True,
    bins=20
)

plt.axvline(
    df["rating"].min(),
    color="red",
    linestyle="--",
    label=f"Min = {df['rating'].min():.2f}"
)

plt.axvline(
    df["rating"].max(),
    color="green",
    linestyle="--",
    label=f"Max = {df['rating'].max():.2f}"
)

plt.title("Distribution of Rating")
plt.xlabel("Rating")
plt.ylabel("Frequency")

plt.legend()

plt.tight_layout()

plt.savefig("rating_distribution.png")

plt.show()


# ─────────────────────────────────────────────
# Distribution du Treatment
# ─────────────────────────────────────────────

print("\nlow_medium_price")

print("Min :", df["low_medium_price"].min())
print("Max :", df["low_medium_price"].max())

print(df["low_medium_price"].value_counts())

plt.figure(figsize=(6,4))

sns.countplot(
    x="low_medium_price",
    data=df
)

plt.title("Distribution of Treatment")

plt.xlabel(
    "0 = High Price    |    1 = Low/Medium Price"
)

plt.ylabel("Count")

plt.tight_layout()

plt.savefig("treatment_distribution.png")

plt.show()