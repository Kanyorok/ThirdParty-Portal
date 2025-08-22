<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TransformApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            $data = $response->getData(true);
            if ($data) {
                $transformedData = $this->transformKeysToCamelCase($data);
                $response->setData($transformedData);
            }
        }

        return $response;
    }

    private function transformKeysToCamelCase(array $array): array
    {
        $transformedArray = [];
        foreach ($array as $key => $value) {
            $camelKey = is_numeric($key) ? $key : Str::camel($key);
            $transformedArray[$camelKey] = is_array($value) ? $this->transformKeysToCamelCase($value) : $value;
        }
        return $transformedArray;
    }
}
