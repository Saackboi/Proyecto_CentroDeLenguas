<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        $payload = [
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status = 400, ?array $errors = null, ?string $code = null): JsonResponse
    {
        $payload = [
            'message' => $message,
        ];

        if ($code !== null) {
            $payload['code'] = $code;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function validation(array $errors, ?string $message = null): JsonResponse
    {
        return self::error($message ?: 'Datos invalidos.', 422, $errors, 'validation_error');
    }

    public static function unauthorized(string $message = 'No autenticado.'): JsonResponse
    {
        return self::error($message, 401, null, 'unauthorized');
    }

    public static function forbidden(string $message = 'No autorizado.'): JsonResponse
    {
        return self::error($message, 403, null, 'forbidden');
    }

    public static function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return self::error($message, 404, null, 'not_found');
    }

    public static function methodNotAllowed(string $message = 'Metodo no permitido.'): JsonResponse
    {
        return self::error($message, 405, null, 'method_not_allowed');
    }

    public static function serverError(string $message = 'Error interno del servidor.'): JsonResponse
    {
        return self::error($message, 500, null, 'server_error');
    }
}
