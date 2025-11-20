<?php

namespace App\Http\Controllers\API\CRDB;

use App\Enums\Core\IntegrationsEnum;
use App\Http\Controllers\Controller;
use App\Models\Settings\APICredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CRDBAuthController extends Controller
{
    /**
     * Login endpoint for CRDB application
     * 
     * This endpoint validates credentials and returns an API token.
     * Credentials can be stored in APICredential configuration or validated against User model.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            // First, try to authenticate using APICredential configuration
            $apiCred = APICredential::query()
                ->where('Integration', IntegrationsEnum::CRDB->value)
                ->latest('Id')
                ->first();

            if ($apiCred) {
                $config = (array) $apiCred->Configuration;
                
                // Check if username/password are stored in configuration
                if (isset($config['username']) && isset($config['password'])) {
                    $storedUsername = $config['username'];
                    $storedPassword = $config['password'];

                    // Support both plain text and hashed passwords
                    $passwordValid = false;
                    if (Hash::needsRehash($storedPassword)) {
                        // Plain text comparison (for initial setup)
                        $passwordValid = ($validated['password'] === $storedPassword);
                    } else {
                        // Hashed password
                        $passwordValid = Hash::check($validated['password'], $storedPassword);
                    }

                    if ($validated['username'] === $storedUsername && $passwordValid) {
                        // For security, API keys cannot be retrieved from the database
                        // Users must use the API key that was shown when it was generated
                        return response()->json([
                            'success' => false,
                            'message' => 'API key cannot be retrieved for security reasons. Please use the API key that was displayed when it was generated, or generate a new one from the Integration settings page.'
                        ], 400);
                    }
                }
            }

            // Fallback: Try to authenticate against User model
            $user = \App\Models\Auth\User::where('Email', $validated['username'])
                ->orWhere('UserID', $validated['username'])
                ->first();

            if ($user && Hash::check($validated['password'], $user->Password)) {
                // Check if user is active
                if (isset($user->IsActive) && !$user->IsActive) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Account is inactive'
                    ], 403);
                }

                // For security, API keys cannot be retrieved from the database
                // Users must use the API key that was shown when it was generated
                return response()->json([
                    'success' => false,
                    'message' => 'API key cannot be retrieved for security reasons. Please use the API key that was displayed when it was generated, or generate a new one from the Integration settings page.'
                ], 400);
            }

            // Authentication failed
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials provided.'],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('CRDB Login Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during authentication',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get API key from APICredential
     * 
     * Note: For security reasons, the full API key is not stored in the database.
     * This method cannot retrieve the full key - it can only validate if a key exists.
     * The key must be generated via the Integration settings page and saved securely by the user.
     */
    private function getApiKey(APICredential $apiCred): string
    {
        $config = (array) $apiCred->Configuration;
        
        // Check if API key configuration exists
        if (!isset($config['Key'])) {
            throw new \Exception('API key not configured. Please generate an API key via the Integration settings page.');
        }

        // For security, we cannot return the full key as it's not stored
        // The user must use the key that was shown when it was generated
        throw new \Exception('API key cannot be retrieved for security reasons. Please generate a new key if you have lost it.');
    }

    /**
     * Get API key for a specific user
     */
    private function getOrGenerateApiKeyForUser($user, ?APICredential $apiCred): string
    {
        if ($apiCred) {
            return $this->getApiKey($apiCred);
        }

        // If no API credential exists, indicate it needs to be set up
        throw new \Exception('CRDB API credentials not configured. Please configure the integration first.');
    }

    /**
     * Validate token endpoint (optional - for token validation)
     */
    public function validateToken(Request $request): JsonResponse
    {
        // This would be handled by the middleware, but we can provide a simple endpoint
        return response()->json([
            'success' => true,
            'message' => 'Token is valid',
        ]);
    }
}

