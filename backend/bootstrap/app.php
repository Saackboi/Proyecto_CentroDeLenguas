<?php

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof ValidationException) {
                return ApiResponse::validation($e->errors(), $e->getMessage());
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::unauthorized('No autenticado.');
            }

            if ($e instanceof AuthorizationException) {
                return ApiResponse::forbidden('No autorizado.');
            }

            if ($e instanceof ModelNotFoundException) {
                return ApiResponse::notFound('Recurso no encontrado.');
            }

            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::notFound('Recurso no encontrado.');
            }

            if ($e instanceof MethodNotAllowedHttpException) {
                return ApiResponse::methodNotAllowed('Metodo no permitido.');
            }

            if ($e instanceof HttpException) {
                $message = $e->getMessage() !== '' ? $e->getMessage() : 'Error en la solicitud.';
                return ApiResponse::error($message, $e->getStatusCode(), null, 'http_error');
            }

            return ApiResponse::serverError('Error interno del servidor.');
        });
    })->create();
