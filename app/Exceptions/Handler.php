<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ApiException;
use App\Traits\ApiErrorResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiErrorResponse;

    public function render($request, Throwable $e)
    {
        // لو الطلب API
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    protected function handleApiException(Throwable $e)
    {
        // 1️⃣ Validation
        if ($e instanceof ValidationException) {
            Log::warning('Validation Error', [
                'errors' => $e->errors(),
                'url' => request()->fullUrl()
            ]);
            return $this->validationErrorResponse($e);
        }

        // 2️⃣ Database Query Exception
        if ($e instanceof QueryException) {
            Log::error('Database Query Exception', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'code' => $e->getCode()
            ]);

            // Duplicate entry error (SQLSTATE 23000)
            if ($e->errorInfo[1] == 1062) {
                return $this->errorResponse(
                    'Duplicate entry detected',
                    Response::HTTP_CONFLICT
                );
            }

            // Foreign key constraint error (SQLSTATE 23000)
            if ($e->errorInfo[1] == 1451 || $e->errorInfo[1] == 1452) {
                return $this->errorResponse(
                    'Database constraint violation',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // General database error
            return $this->serverErrorResponse('Database error occurred');
        }

        // 3️⃣ Custom API Exception
        if ($e instanceof ApiException) {
            Log::info('API Exception', [
                'message' => $e->getMessage(),
                'status' => $e->getStatusCode()
            ]);
            return $this->errorResponse(
                $e->getMessage(),
                $e->getStatusCode(),
                $e->getErrors()
            );
        }

        // 4️⃣ Auth
        if ($e instanceof AuthenticationException) {
            Log::warning('Authentication Failed', [
                'url' => request()->fullUrl()
            ]);
            return $this->unauthorizedResponse('Unauthenticated');
        }

        // 5️⃣ Authorization
        if ($e instanceof AuthorizationException) {
            Log::warning('Authorization Failed', [
                'user' => auth()->id(),
                'url' => request()->fullUrl()
            ]);
            return $this->forbiddenResponse('This action is unauthorized');
        }

        // 6️⃣ Model not found
        if ($e instanceof ModelNotFoundException) {
            Log::notice('Model Not Found', [
                'model' => $e->getModel(),
                'url' => request()->fullUrl()
            ]);
            return $this->notFoundResponse('Resource not found');
        }

        // 7️⃣ Route not found
        if ($e instanceof NotFoundHttpException) {
            Log::notice('Route Not Found', [
                'url' => request()->fullUrl()
            ]);
            return $this->notFoundResponse('Route not found');
        }

        // 8️⃣ Method not allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            Log::warning('Method Not Allowed', [
                'method' => request()->method(),
                'url' => request()->fullUrl()
            ]);
            return $this->errorResponse(
                'Method not allowed',
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }

        // 9️⃣ Rate Limiting (Throttle)
        if ($e instanceof ThrottleRequestsException) {
            Log::warning('Rate Limit Exceeded', [
                'ip' => request()->ip(),
                'url' => request()->fullUrl()
            ]);
            return $this->errorResponse(
                'Too Many Requests',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        // 🔟 General HTTP Exception
        if ($e instanceof HttpException) {
            Log::error('HTTP Exception', [
                'message' => $e->getMessage(),
                'status' => $e->getStatusCode()
            ]);
            return $this->errorResponse(
                $e->getMessage(),
                $e->getStatusCode()
            );
        }

        // 1️⃣1️⃣ Debug mode
        if (config('app.debug')) {
            Log::error('Unhandled Exception (Debug)', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
        }

        // 1️⃣2️⃣ Production fallback
        Log::critical('Unhandled Exception (Production)', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return $this->serverErrorResponse();
    }
}
