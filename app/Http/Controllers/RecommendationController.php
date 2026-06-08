<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RecommendationController extends Controller
{
    public function recommend(Request $request)
    {
        $validated = $request->validate([
            'service_type' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lon' => ['required', 'numeric'],
        ]);

        try {
            return response()->json(
                $this->resolveRecommendation($validated),
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'API Python indisponible',
                'error' => $exception->getMessage(),
            ], 502, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }

    private function resolveRecommendation(array $payload): array
    {
        try {
            return $this->attachInternalArtisanReferences(
                $this->fetchFromPythonApi($payload)
            );
        } catch (\Throwable $exception) {
            Log::warning('Python API unreachable, falling back to local script.', [
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->attachInternalArtisanReferences(
            $this->fetchFromLocalPython($payload)
        );
    }

    private function fetchFromPythonApi(array $payload): array
    {
        $response = Http::timeout(15)->get(
            rtrim(config('services.python_api.url'), '/') . '/recommend',
            [
                'service_type' => $payload['service_type'],
                'lat' => $payload['lat'],
                'lon' => $payload['lon'],
            ]
        );

        if ($response->failed()) {
            throw new \RuntimeException('HTTP ' . $response->status() . ' returned by Python API.');
        }

        return $this->sanitizeUtf8($response->json() ?? []);
    }

    private function fetchFromLocalPython(array $payload): array
    {
        $pythonCode = <<<'PY'
import json
import sys
from recommender import full_recommendation

service_type = sys.argv[1]
lat = float(sys.argv[2])
lon = float(sys.argv[3])

result = full_recommendation(service_type=service_type, client_lat=lat, client_lon=lon)
print(json.dumps(result, ensure_ascii=True))
PY;

        $process = new Process(
            [
                config('services.python_api.binary', 'python3'),
                '-c',
                $pythonCode,
                $payload['service_type'],
                (string) $payload['lat'],
                (string) $payload['lon'],
            ],
            base_path()
        );

        $process->setTimeout(30);
        $process->setEnv([
            'PYTHONUTF8' => '1',
            'PYTHONIOENCODING' => 'utf-8',
        ]);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Local Python execution failed.');
        }

        $output = trim($process->getOutput());
        $decoded = json_decode($output, true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON returned by local Python execution.');
        }

        return $this->sanitizeUtf8($decoded);
    }

    private function sanitizeUtf8(mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $item) {
                $sanitizedKey = is_string($key)
                    ? mb_convert_encoding($key, 'UTF-8', 'UTF-8')
                    : $key;

                $sanitized[$sanitizedKey] = $this->sanitizeUtf8($item);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $value;
    }

    private function attachInternalArtisanReferences(array $payload): array
    {
        foreach (['meilleurs_artisans', 'services_complementaires'] as $key) {
            if (! isset($payload[$key]) || ! is_array($payload[$key])) {
                continue;
            }

            $payload[$key] = array_map(function (array $artisan) {
                $matchedArtisan = $this->findInternalArtisan($artisan);

                if (! $matchedArtisan) {
                    return $artisan;
                }

                return array_merge($artisan, [
                    'external_artisan_id' => $artisan['artisan_id'] ?? null,
                    'id' => $matchedArtisan->id,
                    'artisan_id' => $matchedArtisan->id,
                    'user_id' => $matchedArtisan->user_id,
                    'artisan_name' => $matchedArtisan->name ?: ($artisan['artisan_name'] ?? null),
                    'service_type' => $matchedArtisan->service_type ?: ($artisan['service_type'] ?? null),
                    'commune' => $matchedArtisan->commune ?: ($artisan['commune'] ?? null),
                    'city' => $matchedArtisan->city ?: ($artisan['city'] ?? null),
                    'description' => $matchedArtisan->description ?: ($artisan['description'] ?? null),
                    'photo' => $matchedArtisan->user?->art?->photo ? asset('storage/' . $matchedArtisan->user->art->photo) : null,
                ]);
            }, $payload[$key]);
        }

        return $payload;
    }

    private function findInternalArtisan(array $artisan): ?Artisan
    {
        $query = Artisan::query()->with('user.art');

        if (! empty($artisan['artisan_name'])) {
            $query->where('name', $artisan['artisan_name']);
        }

        if (! empty($artisan['service_type'])) {
            $query->where('service_type', $artisan['service_type']);
        }

        $commune = $artisan['commune'] ?? null;
        $city = $artisan['city'] ?? null;

        if ($commune || $city) {
            $query->where(function ($builder) use ($commune, $city) {
                if ($commune) {
                    $builder->orWhere('commune', $commune);
                }

                if ($city) {
                    $builder->orWhere('city', $city);
                }
            });
        }

        return $query->first();
    }
}
