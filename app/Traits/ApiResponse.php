<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Response sukses
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'status'  => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Response error
     */
    protected function error(
        string $message = 'Error',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status'  => false,
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Response dengan pagination
     */
    protected function paginated(
        mixed $data,
        string $message = 'Success'
    ): JsonResponse {
        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => $message,
            'data'    => $data->items(),
            'meta'    => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'last_page'    => $data->lastPage(),
            ],
        ]);
    }
}
