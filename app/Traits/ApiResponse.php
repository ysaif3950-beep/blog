<?php

declare(strict_types=1);

namespace App\Traits;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Http\JsonResponse;
 use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'Mission completed successfully',
        int $code = 200
    ): JsonResponse {

        $response = [
            'status'  => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function error(
        string $message = 'Something went wrong',
        int $code = 400,
        ?array $errors = null
    ): JsonResponse {

        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

   protected function created(
    mixed $data = null,
    string $message = 'Resource created successfully'
): JsonResponse {

    return $this->success($data, $message, 201);
}


protected function updated(
    mixed $data=null,
    string $message='updated successfully'
):JsonResponse{
    return $this->success($data,$message,200);
}

protected function deleted(string $message = 'Resource deleted successfully'): JsonResponse
{
    return response()->json([
        'status' => 'success',
        'message' => $message,
    ], 200);
}

protected function notFound(string $message = 'Resource not found'): JsonResponse
{
    return $this->error($message, 404);
}

protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
{
    return $this->error($message, 401);
}

protected function forbidden(string $message = 'Forbidden'): JsonResponse
{
    return $this->error($message, 403);
}

protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
{
    return $this->error($message, 422, $errors);
}

protected function noContent(): JsonResponse
{
    return response()->json(null, 204);
}

/**
 * Return success response with a single Resource
 */
protected function successWithResource(
    JsonResource $resource,
    string $message = 'Data retrieved successfully',
    int $code = 200
): JsonResponse {
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $resource,
    ], $code);
}

/**
 * Return success response with Resource Collection (non-paginated)
 */
protected function successWithCollection(
    $collection,
    string $message = 'Data retrieved successfully',
    int $code = 200
): JsonResponse {
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $collection,
    ], $code);
}

/**
 * Return paginated response with meta and links
 * Works with both regular paginated data and Resource collections
 */
protected function paginated(
    $paginatedData,
    string $message = 'Data retrieved successfully'
): JsonResponse {
    // If it's a ResourceCollection, get the underlying paginator
    if ($paginatedData instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
        $paginator = $paginatedData->resource;
    } else {
        $paginator = $paginatedData;
    }

    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $paginatedData instanceof \Illuminate\Http\Resources\Json\ResourceCollection 
            ? $paginatedData->items() 
            : $paginator->items(),
        'meta' => [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ],
        'links' => [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ]
    ], 200);
}


}
