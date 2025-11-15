<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait ApiExceptionHandler
{
    /**
     * Render API exceptions
     */
    protected function renderApiException($request, Exception $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Phone not found',
                'error_code' => 'PHONE_NOT_FOUND'
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error_code' => 'ENDPOINT_NOT_FOUND'
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error_code' => 'METHOD_NOT_ALLOWED'
            ], 405);
        }

        // Default error response
        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
            'error_code' => 'INTERNAL_ERROR'
        ], 500);
    }
}