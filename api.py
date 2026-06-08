from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from recommender import full_recommendation

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://127.0.0.1:8000",
        "http://localhost:8000",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/recommend")
def recommend(service_type: str, lat: float, lon: float, max_distance_km: float = 30):
    return full_recommendation(
        service_type=service_type,
        client_lat=lat,
        client_lon=lon,
        max_distance_km=max_distance_km,
    )