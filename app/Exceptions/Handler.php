<?php

namespace App\Exceptions;

use App\Transformers\Json;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $rendered = parent::render($request, $exception);

        if ($exception instanceof MethodNotAllowedHttpException) {
            $output = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => [],
            ];
        } else if ($exception instanceof NotFoundHttpException) {
            $output = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => [],
            ];
        } else if ($exception instanceof ValidationException) {
            $output = [
                'status' => 422,
                'message' => $exception->getMessage(),
                'data' => $exception->validator->errors(),
            ];
        } else if ($exception instanceof ErrorException) {
            $output = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => [],
            ];
        } else if ($exception instanceof UnauthorizedHttpException) {
            $output = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => [],
            ];
        } else if ($exception instanceof TokenBlacklistedException) {
            $output = [
                'status' => $rendered->getStatusCode(),
                'message' => $exception->getMessage(),
                'data' => [],
            ];
        } else {
            $output = [
                'status' => $rendered->getStatusCode(),
                'message' => $rendered->getContent(),
                'data' => [],
            ];
        }

        return !$request->expectsJson()
            ? $rendered
            : response()->json(Json::response($output['status'], $output['data'], $output['message']), $output['status']);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest(url('/login'));
    }
}
