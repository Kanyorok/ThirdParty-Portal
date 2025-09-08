<?php

namespace App\Http\Middleware;

use App\Enums\Core\IntegrationsEnum;
use App\Models\Settings\APICredential;
use Closure;
use Exception;
use Illuminate\Http\Request;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
use Symfony\Component\HttpFoundation\Response;

class DocuwareAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Basic ')) {
            $encodedCredentials = substr($authHeader, 6);
            $credentials = base64_decode($encodedCredentials);
            [$parsedUsername, $parsedPassword] = explode(':', $credentials, 2);

            try {
                $ApiCred = APICredential::query()->where('Integration', IntegrationsEnum::DMSCoreBanking->value)->latest('Id')->first();
                if (!$ApiCred instanceof APICredential) {
                    abort(Response::HTTP_UNAUTHORIZED);
                }
                $user = $ApiCred->Configuration?->user;
                $pass = $ApiCred->Configuration?->pass;
                if ($user !== $parsedUsername || $pass !== $parsedPassword) {
                    abort(Response::HTTP_UNAUTHORIZED);
                }
                return $next($request);
            } catch (ConnectionException|InvalidParameterException|Exception) {
            }
        }
        abort(Response::HTTP_UNAUTHORIZED);
    }
}
