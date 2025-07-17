<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransformApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->method() !== 'GET') {
            $input = $request->all();
            $transformedInput = $this->transformKeysToCamelCase($input);
            $request->replace($transformedInput);
        }

        return $next($request);
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
