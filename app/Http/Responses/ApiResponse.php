<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Respuesta exitosa estándar
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $data
     * @return JsonResponse
     */
    public static function success(string $message = 'Éxito', int $statusCode = 200, mixed $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Respuesta de error estándar
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $data
     * @return JsonResponse
     */
    public static function error(string $message = 'Error del servidor', int $statusCode = 500, mixed $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Respuesta de validación fallida
     *
     * @param array $errors
     * @return JsonResponse
     */
    public static function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $errors
        ], 422);
    }

    /**
     * Respuesta de recurso no encontrado
     *
     * @param string $resource
     * @return JsonResponse
     */
    public static function notFound(string $resource = 'Recurso'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "{$resource} not Found"
        ], 404);
    }

    /**
     * Respuesta de recurso creado
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function created(string $message = 'Recurso creado exitosamente', mixed $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 201);
    }
}