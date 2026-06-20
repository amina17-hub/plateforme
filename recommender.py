import os
import unicodedata
from functools import lru_cache
from math import asin, cos, radians, sin, sqrt
from pathlib import Path

import pandas as pd
from joblib import load


BASE_DIR = Path(__file__).resolve().parent
DATASET_PATH = BASE_DIR / "storage" / "skikda_unified_dataset2.csv"
DEFAULT_MODELS_DIR = BASE_DIR / "models"

# Liste mise à jour avec les vrais noms de fichiers générés par causalinference.py
MODEL_FILENAMES = [
    "ate_results.pkl",
    "consensus_ate.pkl",
    "xgb_classifier.pkl",
    "s_learner_xgb.pkl",
    "t_learner_xgb_treated.pkl",
    "t_learner_xgb_control.pkl",
]


def _resolve_models_dir() -> Path:
    env_dir = os.getenv("PYTHON_MODEL_DIR")
    if env_dir:
        candidate = Path(env_dir).expanduser()
        if candidate.exists():
            return candidate
    if DEFAULT_MODELS_DIR.exists():
        return DEFAULT_MODELS_DIR
    return BASE_DIR / "models"


@lru_cache(maxsize=1)
def _load_models():
    models_dir = _resolve_models_dir()
    models = {}

    for filename in MODEL_FILENAMES:
        file_path = models_dir / filename
        if not file_path.exists():
            continue
        try:
            models[filename] = load(file_path)
        except Exception:
            continue

    return models


@lru_cache(maxsize=1)
def _load_dataset() -> pd.DataFrame:
    df = pd.read_csv(DATASET_PATH, encoding="utf-8-sig").copy()

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
        if col in df.columns:
            df[col] = (
                df[col]
                .fillna("")
                .astype(str)
                .str.normalize("NFKC")
                .str.strip()
            )

    numeric_cols = ["price", "rating", "latitude", "longitude"]
    for col in numeric_cols:
        if col in df.columns:
            df[col] = pd.to_numeric(df[col], errors="coerce")

    df = df.dropna(subset=["service_type", "latitude", "longitude"]).copy()

    if "commune" not in df.columns:
        df["commune"] = df.get("city", "")

    if "artisan_name" not in df.columns:
        df["artisan_name"] = "Artisan"

    if "artisan_id" not in df.columns:
        df["artisan_id"] = range(1, len(df) + 1)

    if "price" not in df.columns:
        df["price"] = 0.0
    if "rating" not in df.columns:
        df["rating"] = 0.0

    df["price"] = df["price"].fillna(df.groupby("service_type")["price"].transform("median"))
    df["price"] = df["price"].fillna(df["price"].median()).fillna(0)

    df["rating"] = df["rating"].fillna(df.groupby("service_type")["rating"].transform("median"))
    df["rating"] = df["rating"].fillna(df["rating"].median()).fillna(0)

    df["service_type_code"] = df["service_type"].astype("category").cat.codes
    df["commune_code"] = df["commune"].astype("category").cat.codes

    median_price = float(df["price"].median())
    df["low_medium_price"] = (df["price"] <= median_price).astype(int)
    df["high_price"] = 1 - df["low_medium_price"]

    return df


def _haversine_distance(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
    radius_km = 6371.0
    delta_lat = radians(lat2 - lat1)
    delta_lon = radians(lon2 - lon1)
    start = radians(lat1)
    end = radians(lat2)
    a = sin(delta_lat / 2) ** 2 + cos(start) * cos(end) * sin(delta_lon / 2) ** 2
    return radius_km * 2 * asin(sqrt(a))


def _normalize_text(value: str) -> str:
    normalized = unicodedata.normalize("NFKD", str(value or "").strip().lower())
    return "".join(char for char in normalized if not unicodedata.combining(char))


def _normalize(series: pd.Series, inverse: bool = False) -> pd.Series:
    minimum = series.min()
    maximum = series.max()

    if pd.isna(minimum) or pd.isna(maximum) or minimum == maximum:
        return pd.Series([1.0] * len(series), index=series.index)

    normalized = (series - minimum) / (maximum - minimum)
    return 1 - normalized if inverse else normalized


def _predict(model, features: pd.DataFrame, default: float = 0.0) -> float:
    try:
        prediction = model.predict(features)
        return float(prediction[0])
    except Exception:
        return default


def _predict_proba_positive(model, features: pd.DataFrame, default: float = 0.5) -> float:
    try:
        probabilities = model.predict_proba(features)
        return float(probabilities[0][1])
    except Exception:
        return default


causal_logic = [
    ["Maçon", "Plâtrier / Staffeur", "Carreleur", "Peintre", "Électricien", "Plombier"],
    ["Chauffagiste", "Climatisation / Frigoriste", "Réparation Électroménager"],
    ["Menuisier", "Ébéniste", "Décorateur d'intérieur", "Tapissier / Couturier (Linge de Maison)"],
    ["Étanchéiste", "Charpentier", "Serrurier", "Vitrailler / Miroitier"],
    ["Organisation d'événements", "Traiteur / Pâtissier", "Coiffure à Domicile", "Esthéticienne à Domicile"],
    ["Jardinier / Paysagiste", "Piscines (Constructeur/Maintenance)", "Dératiseur / Désinsectiseur", "Nettoyage professionnel (Spécialisé)"],
    ["Soutien Scolaire", "Fabrication de parfums", "Fabrication de Bougies", "Cosmétiques (Crèmes hydratantes)"],
    ["Installateur Systèmes Sécurité", "Antenniste", "Serrurier"],
    ["Aide-Ménagère Spécialisée", "Restauration de vêtements", "Nettoyage professionnel (Spécialisé)"],
]


def _build_artisan_output(row: pd.Series, median_price: float) -> dict:
    return {
        "artisan_id": int(row["artisan_id"]),
        "artisan_name": row["artisan_name"],
        "service_type": row["service_type"],
        "commune": row["commune"],
        "city": row.get("city", "Skikda"),
        "latitude": float(row["latitude"]),
        "longitude": float(row["longitude"]),
        "price": round(float(row["price"]), 2),
        "rating": round(float(row["rating"]), 2),
        "distance_km": round(float(row["distance_km"]), 2),
        "causal_score": float(row["causal_score"]),
        "price_category": "Bas/Moyen" if float(row["price"]) <= median_price else "Élevé",
    }


def _score_artisans(
    artisans: pd.DataFrame,
    consensus_ate: float,
    median_price: float,
    client_lat: float,
    client_lon: float,
    max_distance_km: float = 30,
    top_n: int = 10,
) -> list[dict]:
    if artisans.empty:
        return []

    artisans = artisans.copy()
    if "artisan_id" in artisans.columns:
        artisans = artisans.drop_duplicates(subset=["artisan_id"]).copy()

    artisans["distance_km"] = artisans.apply(
        lambda row: _haversine_distance(client_lat, client_lon, float(row["latitude"]), float(row["longitude"])),
        axis=1,
    )

    in_radius = artisans[artisans["distance_km"] <= max_distance_km].copy()
    if not in_radius.empty:
        artisans = in_radius
    else:
        artisans = artisans.nsmallest(min(top_n * 5, len(artisans)), "distance_km").copy()

    artisans["rating_score"] = _normalize(artisans["rating"])
    artisans["price_score"] = _normalize(artisans["price"], inverse=True)
    artisans["distance_score"] = _normalize(artisans["distance_km"], inverse=True)
    
    # Application du bonus causal si le prix est inférieur ou égal au prix médian (Traitement T=1)
    artisans["causal_bonus"] = artisans["price"].apply(lambda price: max(consensus_ate, 0.0) if float(price) <= median_price else 0.0)

    # Calcul du score combiné
    artisans["causal_score"] = (
        artisans["rating_score"] * 0.35
        + artisans["price_score"] * 0.20
        + artisans["distance_score"] * 0.25
        + artisans["causal_bonus"] * 0.40
    ).round(4)

    artisans = artisans.sort_values(
        by=["causal_score", "rating", "distance_km"],
        ascending=[False, False, True],
    ).head(top_n)

    return [_build_artisan_output(row, median_price) for _, row in artisans.iterrows()]


def _recommend_complementary_services(
    current_service: str,
    client_lat: float,
    client_lon: float,
    df: pd.DataFrame,
    consensus_ate: float,
    max_distance_km: float = 30,
    top_n: int = 1,
) -> list[dict]:
    recommendations = []
    seen_services = set()
    current_service_normalized = _normalize_text(current_service)
    median_price = float(df["price"].median())

    for workflow in causal_logic:
        workflow_normalized = [_normalize_text(w) for w in workflow]
        if current_service_normalized in workflow_normalized:
            current_index = workflow_normalized.index(current_service_normalized)
            for service in workflow[current_index + 1:]:
                service_key = _normalize_text(service)
                if service_key in seen_services:
                    continue

                artisans = df[df["service_type"].apply(_normalize_text) == service_key].copy()
                best = _score_artisans(artisans, consensus_ate, median_price, client_lat, client_lon, max_distance_km, top_n)
                if best:
                    best[0]["recommended_because"] = f"Service complementaire recommande : {service}"
                    recommendations.append(best[0])
                    seen_services.add(service_key)
            break

    if not recommendations:
        fallback_services = [
            "Nettoyage professionnel (Spécialisé)",
            "Serrurier",
            "Électricien",
        ]
        for service in fallback_services:
            service_key = _normalize_text(service)
            if service_key == current_service_normalized or service_key in seen_services:
                continue
            artisans = df[df["service_type"].apply(_normalize_text) == service_key].copy()
            best = _score_artisans(artisans, consensus_ate, median_price, client_lat, client_lon, max_distance_km, top_n)
            if best:
                best[0]["recommended_because"] = f"Service recommande : {service}"
                recommendations.append(best[0])
                seen_services.add(service_key)

    return recommendations


def full_recommendation(service_type: str, client_lat: float, client_lon: float, max_distance_km: float = 30):
    df = _load_dataset()
    models = _load_models()
    requested_service = service_type.strip()
    
    # On récupère la valeur consensus stockée dans le dictionnaire ou le fichier direct
    consensus_ate = models.get("consensus_ate.pkl", 0.0)
    requested_service_normalized = _normalize_text(requested_service)

    artisans = df[df["service_type"].apply(_normalize_text) == requested_service_normalized].copy()
    if artisans.empty:
        return {
            "service_demande": requested_service,
            "ate_consensus": None,
            "meilleurs_artisans": [],
            "services_complementaires": [],
            "message": "Aucun artisan trouve pour ce service.",
        }

    meilleurs_artisans = _score_artisans(
        artisans,
        consensus_ate=consensus_ate,
        median_price=float(df["price"].median()),
        client_lat=client_lat,
        client_lon=client_lon,
        max_distance_km=max_distance_km,
        top_n=10,
    )

    services_complementaires = _recommend_complementary_services(
        current_service=requested_service,
        client_lat=client_lat,
        client_lon=client_lon,
        df=df,
        consensus_ate=consensus_ate,
        max_distance_km=max_distance_km,
        top_n=1,
    )

    return {
        "service_demande": requested_service,
        "ate_consensus": float(consensus_ate),
        "meilleurs_artisans": meilleurs_artisans,
        "services_complementaires": services_complementaires,
    }