<?php

namespace App\Http\Middleware;

use App\Enums\Core\IntegrationsEnum;
use App\Enums\Core\SystemIntegrationEnum;
use App\Helpers\SystemHelper;
use App\Models\Settings\APICredential;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use Symfony\Component\HttpFoundation\Response;

class CRDBAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->ajax() && ! $request->expectsJson()) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $source = $request->header('x-source');
        if (! is_string($source) || ! in_array($source, [
            SystemIntegrationEnum::CRDB->value,
            IntegrationsEnum::CRDB->value,
        ], true)) {
            return $this->_fail('client: no source');
        }
        $bearerToken = $request->bearerToken();
        if (! is_string($bearerToken)) {
            return $this->_fail('client: no token provided');
        }

        try {
            $ApiCred = APICredential::query()->where('Integration', IntegrationsEnum::CRDB->value)->latest('Id')->first();
            if (! $ApiCred instanceof APICredential) {
                return $this->_fail('No API credential Found');
            }
            $key = $ApiCred->Configuration?->Key;
            if (! is_string($key)) {
                return $this->_fail('Invalid key in system');
            }
        } catch (ConnectionException | InvalidParameterException | Exception $e) {
            return $this->_fail($e->getMessage());
        }
        if (md5($bearerToken) === $key) {
            return $next($request);
        }

        return $this->_fail('client: invalid key');
    }

    protected function _fail(string $reason): JsonResponse
    {
        SystemHelper::notifyAdmin('CRDB Endpoints Authentication Failure : ' . $reason);

        return response()->json(['message' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
}
