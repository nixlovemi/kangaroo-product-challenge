<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $statusCode = 200,
    ): JsonResponse {
        return $this->buildResponse(true, $message, $data, $statusCode);
    }

    protected function errorResponse(
        string $message = 'Operation failed',
        int $statusCode = 400,
        mixed $errors = null,
    ): JsonResponse {
        return $this->buildResponse(false, $message, null, $statusCode, $errors);
    }

    private function buildResponse(
        bool $success,
        string $message,
        mixed $data,
        int $statusCode,
        mixed $errors = null,
    ): JsonResponse {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
