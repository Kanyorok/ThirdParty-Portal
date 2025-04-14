<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\ControllerMiddlewareOptions;

abstract class Controller //implements HasMiddleware
{
    use AuthorizesRequests;

    /**
     * Get the middleware that should be assigned to the controller.
     */
    /*  public static function middleware(): array
      {
          return [];
      }*/

    /**
     * The middleware registered on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Register middleware on the controller.
     *
     * @param array|\Closure|string $middleware
     * @param array $options
     * @return \Illuminate\Routing\ControllerMiddlewareOptions
     */
    public function middleware(array|\Closure|string $middleware, array $options = []): ControllerMiddlewareOptions
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = [
                                   'middleware' => $m,
                                   'options'    => &$options,
                                  ];
        }

        return new ControllerMiddlewareOptions($options);
    }

    /**
     * Get the middleware assigned to the controller.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        return $this->{$method}(...array_values($parameters));
    }


    public function succeeded(string $message, string $route = '', array $data = [], int $status = 202): JsonResponse
    {
        return response()->json(array_merge(
            ['message' => $message],
            ($route === '') ? [] : [ 'route' => $route],
            $data
        ), $status);
    }

    public function errored(string $message, array $data = [], int $status = 400): JsonResponse
    {
        return response()->json(array_merge(['message' => $message], $data), $status);
    }

    public function br_response(string $status, string $message, array $extra = []): JsonResponse
    {
        return response()->json([
                                 'ourBranchID' => null,
                                 'resp'        => [
                                                   'status'     => $status,
                                                   'outputJSON' => (!empty($extra)) ? json_encode($extra) : '',
                                                   'message'    => $message,
                                                  ],
                                ]);
    }
}
