<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class JsonResponseService
{
    public function createJsonResponse(
        $data = null,
        string $status = "success",
        ?array $errors = null,
        ?string $token = null,
        ?string $permission = null,
        array $customHeaders = []
    ): JsonResponse {
        $finalStatus = $status;
        if (is_array($data) && isset($data['success']) && $data['success'] === false) {
            $finalStatus = 'error';
        }

        $json = [
            "status" => $finalStatus,
            "data" => $data,
            "errors" => $this->formatErrors($errors),
        ];

        if ($token) {
            $json['token'] = $token;
        }
        if ($permission) {
            $json['permission'] = $permission;
        }

        $response = response()->json($json);

        $this->addCorsHeaders($response);

        foreach ($customHeaders as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    /**
     * Formate les erreurs de manière standardisée
     */
    private function formatErrors(?array $errors): ?array
    {
        if (empty($errors)) {
            return null;
        }

        return array_map(function ($error) {
            // Gère différents formats d'erreurs
            if (is_string($error)) {
                return [
                    "code" => null,
                    "field" => null,
                    "message" => $error,
                    "details" => null,
                ];
            }

            return [
                "code" => $error['code'] ?? null,
                "field" => $error['field'] ?? null,
                "message" => $error['message'] ?? '',
                "details" => $error['details'] ?? null,
            ];
        }, $errors);
    }

    /**
     * Ajoute les en-têtes CORS standard
     */
    private function addCorsHeaders($response): void
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}
