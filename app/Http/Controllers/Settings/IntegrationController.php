<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Core\IntegrationsEnum;
use App\Enums\EmailEncryptionEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\IntegrationRequest;
use App\Models\Auth\User;
use App\Models\Settings\APICredential;
use App\Services\CRMEmailService;
use App\Services\ThirdParty\AIService;
use App\Services\ThirdParty\CSSMSService;
use App\Services\ThirdParty\FacebookService;
use App\Services\ThirdParty\InfobipService;
use App\Services\ThirdParty\iTrackService;
use App\Services\ThirdParty\SSRSService;
use App\Services\ThirdParty\TwitterService;
use EchoLabs\Prism\Enums\Provider;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;
use Throwable;

class IntegrationController extends Controller
{
    /**
     * Handle the incoming request.
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function __invoke(IntegrationRequest $request): JsonResponse
    {
        $this->authorize('create', APICredential::class);
        try {
            $Integration = $request->getIntegration();
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        if ($Integration->value === IntegrationsEnum::Email->value) {
            return $this->_saveEmailConfiguration($request, $request->getIncomingEncryption(), $request->getOutgoingEncryption());
        }

        if ($Integration->value === IntegrationsEnum::InfoBip->value) {
            return $this->_infoBipConfiguration($request->validated('InfoBip_Host'), $request->validated('InfoBip_Email'), $request->validated('InfoBip_API_Key'), $request->user());
        }

        if ($Integration->value === IntegrationsEnum::iTrack->value) {
            return $this->_iTrackConfiguration($request->getITrackUrl(), $request->validated('iTrack_Username'), $request->validated('iTrack_Password'), $request->user());
        }

        if ($Integration->value === IntegrationsEnum::LLM->value) {
            return $this->_llmConfiguration($request->getAIProvider(), $request->validated('LLM_Model'), $request->validated('LLM_API_Key'), $request->user());
        }

        if ($Integration->value === IntegrationsEnum::SMS->value) {
            return $this->_saveSMSConfiguration($request->validated('SMS_Priority'), $request->validated('SMS_Message_Type'), $request->validated('SMS_Sender_Id'), $request->validated('SMS_Password'), $request->user());
        }

        if ($Integration->value === IntegrationsEnum::Channels->value) {
            return $this->_generateChannelKey($request, $request->getChannelCallback());
        }

        if ($Integration->value === IntegrationsEnum::Facebook->value) {
            return $this->_saveFacebookConfiguration($request->validated('FB_App_Id'), $request->validated('FB_Page_Id'), $request->validated('FB_App_Secret'), $request->validated('FB_Page_Token'), $request->user());
        }

        if ($Integration->value === IntegrationsEnum::Twitter->value) {
            return $this->_saveXConfiguration(
                $request->validated('X_Access_Token'),
                $request->validated('X_Access_Token_Secret'),
                $request->validated('X_Consumer_Key'),
                $request->validated('X_Consumer_Secret'),
                $request->validated('X_Bearer_Token'),
                $request->validated('X_Is_Free') === 'yes',
                $request->user()
            );
        }

        if ($Integration->value === IntegrationsEnum::CoreBanking->value) {
            return $this->_coreBankingConfiguration($request->getCBSHost(), $request->validated('CBS_ConsumerKey'), $request->validated('CBS_ConsumerSecret'), $request->user());
        }

        if ($Integration->value === IntegrationsEnum::ReportService->value) {
            return $this->_reportServiceConfiguration($request->getSSRS_Host(), $request->validated('SSRS_Path'), $request->validated('SSRS_Username'), $request->validated('SSRS_Password'), $request->user());
        }

        if (in_array($Integration->value, [IntegrationsEnum::Website->value, IntegrationsEnum::PBX->value, IntegrationsEnum::CRDB->value], true)) {
            return $this->_generateKey($request, $Integration);
        }

        if ($Integration->value === IntegrationsEnum::Organization->value) {
            return $this->_saveOrganizationBranding(
                $request->validated('Org_Name'),
                $request->validated('Org_Motto'),
                $request->validated('Org_Logo'),
                $request->user()
            );
        }

        return $this->errored('integration not complete');
    }

    private function _saveEmailConfiguration(IntegrationRequest $request, EmailEncryptionEnum $Incoming_Encryption, EmailEncryptionEnum $Outgoing_Encryption): JsonResponse
    {
        if (!CRMEmailService::testConfig($request->validated('Outgoing_Server'), $request->validated('Outgoing_Port'), $Outgoing_Encryption, $request->validated('Outgoing_Username'), $request->validated('Outgoing_Password'))) {
            return $this->errored('invalid configuration check.');
        }

        $data = [
            'Incoming' => [
                'host' => $request->validated('Incoming_Server'),
                'port' => $request->validated('Incoming_Port'),
                'folder' => 'INBOX',
                'username' => $request->validated('Incoming_Username'),
                'password' => $request->validated('Incoming_Password'),
                'encryption' => $Incoming_Encryption->value,
            ],
            'Outgoing' => [
                'host' => $request->validated('Outgoing_Server'),
                'port' => $request->validated('Outgoing_Port'),
                'username' => $request->validated('Outgoing_Username'),
                'password' => $request->validated('Outgoing_Password'),
                'encryption' => $Outgoing_Encryption->value,
            ],
        ];

        return $this->_saveData(IntegrationsEnum::Email, $data, $request->user());
    }

    protected function _saveData(IntegrationsEnum $Integration, array $data, User $actor): JsonResponse
    {
        try {
            DB::transaction(static function () use ($Integration, $data, $actor) {
                APICredential::query()->where('Integration', $Integration->value)->update([
                    'DeletedBy' => $actor->Id,
                ]);
                APICredential::query()->where('Integration', $Integration->value)->delete();

                $crmIntegration = APICredential::create([
                    'Integration' => $Integration->value,
                    'Configuration' => $data,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($crmIntegration->refresh())->event('updated')->log('Set Updated Integration Config for: ' . $Integration->description());
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error updating ' . $Integration->name . ' config failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded($Integration->description() . ' updated.');
    }

    private function _infoBipConfiguration(string $host, string $email, #[SensitiveParameter] string $APIKey, User $actor): JsonResponse
    {
        try {
            if (InfobipService::testConfig($host, $email, $APIKey, $actor)) {
                return $this->_saveData(IntegrationsEnum::InfoBip, [
                    'Host' => $host,
                    'Email' => $email,
                    'Key' => $APIKey,
                ], $actor);
            }
        } catch (Exception $e) {
            return $this->errored($e->getMessage());
        }
        return $this->errored('invalid configuration check.');
    }

    private function _llmConfiguration(Provider $provider, string $model, #[SensitiveParameter] string $APIKey, User $actor): JsonResponse
    {
        try {
            if (AIService::testConfig($provider, $model, $APIKey)) {
                return $this->_saveData(IntegrationsEnum::LLM, [
                    'Model' => $model,
                    'Provider' => $provider->value,
                    'Config' => ['api_key' => $APIKey],
                ], $actor);
            }
        } catch (Exception $e) {
            return $this->errored($e->getMessage());
        }
        return $this->errored('invalid configuration check.');
    }

    private function _saveSMSConfiguration(string $priority, string $messageType, #[SensitiveParameter] string $sender_id, #[SensitiveParameter] string $password, User $actor): JsonResponse
    {
        if (!CSSMSService::testConfig($priority, $messageType, $sender_id, $password, $actor)) {
            return $this->errored('invalid configuration check.');
        }

        return $this->_saveData(IntegrationsEnum::SMS, [
            'priority' => $priority,
            'messageType' => $messageType,
            'sender_Id' => $sender_id,
            'password' => $password,
        ], $actor);
    }

    private function _generateChannelKey(IntegrationRequest $request, string $callbackUrl): JsonResponse
    {
        $key = base64_encode(Str::random(64));
        $data = [
            'Key' => md5($key),
            'Callback' => $callbackUrl,
        ];
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($data, $actor) {
                $Integration = IntegrationsEnum::Channels;
                APICredential::query()->where('Integration', $Integration->value)->update([
                    'DeletedBy' => $actor->Id,
                ]);
                APICredential::query()->where('Integration', $Integration->value)->delete();

                $crmIntegration = APICredential::create([
                    'Integration' => $Integration->value,
                    'Configuration' => $data,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($crmIntegration->refresh())->event('updated')->log('Generated a new channels api key.');
            });
        } catch (Exception|Throwable $e) {
            Log::error('Error updating Channel config failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('channels key generated.', data: ['token' => $key]);
    }

    private function _saveFacebookConfiguration(int $appId, int $pageId, #[SensitiveParameter] string $appSecret, #[SensitiveParameter] string $pageToken, User $actor): JsonResponse
    {
        try {
            $service = new FacebookService($appId, $appSecret, $pageToken, $pageId);
            //check permission
            /* $permissions = $service->pagePermissions();
             if (count($permissions) !== 0) {
                 return $this->errored('token does not ha the following permissions '.implode(', ', $permissions),);
             }*/
            //get a long live token which tests config
            $tokenResponse = $service->refreshToken();//{ "access_token": "", "token_type": "bearer", "expires_in": (int) (timestamp + this to get date) }
            $pageName = (new FacebookService($appId, $appSecret, $tokenResponse->access_token, $pageId))->getPage()?->name;//this is is to confirm new token is valid.
        } catch (ErroredException $e) {
            Log::error('Error updating facebook configuration failed: ');
            Log::error($e);
            return $this->errored('the given credentials are invalid.');
        }

        return $this->_saveData(IntegrationsEnum::Facebook, [
            'page_id' => $pageId,
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'page_token' => $tokenResponse->access_token,
            'page_token_expires_at' => bcadd($tokenResponse->expires_in, now()->format('U')),
            'page_name' => $pageName,
        ], $actor);
    }

    private function _saveXConfiguration(#[SensitiveParameter] string $accessToken, #[SensitiveParameter] string $accessTokenSecret, #[SensitiveParameter] string $consumerKey, #[SensitiveParameter] string $consumerSecret, #[SensitiveParameter] string $bearerToken, bool $isFree, User $actor): JsonResponse
    {
        try { //get username and userid
            $userResponse = (new TwitterService($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $bearerToken))->getUser();
        } catch (ErroredException $e) {
            Log::error('Error updating twitter configuration failed: ');
            Log::error($e);
            return $this->errored('the given credentials are invalid.');
        }

        return $this->_saveData(IntegrationsEnum::Twitter, [
            'account_id' => $userResponse?->data->id,
            'username' => $userResponse->data->username,
            'name' => $userResponse->data->name,
            'access_token' => $accessToken,
            'access_token_secret' => $accessTokenSecret,
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'bearer_token' => $bearerToken,
            'is_free' => $isFree,
        ], $actor);
    }

    private function _coreBankingConfiguration(string $Host, #[SensitiveParameter] string $ConsumerKey, #[SensitiveParameter] string $ConsumerSecret, User $actor): JsonResponse
    {
        return $this->_saveData(IntegrationsEnum::CoreBanking, [
            'host' => $Host,
            'ConsumerKey' => $ConsumerKey,
            'ConsumerSecret' => $ConsumerSecret,
        ], $actor);
    }

    private function _generateKey(IntegrationRequest $request, IntegrationsEnum $Integration): JsonResponse
    {
        $key = base64_encode(Str::random(64));
        $actor = $request->user();
        try {
            DB::transaction(function () use ($Integration, $key, $actor) {
                APICredential::query()->where('Integration', $Integration->value)->update([
                    'DeletedBy' => $actor->Id,
                ]);
                APICredential::query()->where('Integration', $Integration->value)->delete();

                $configuration = ['Key' => md5($key)];
                
                // For CRDB, store only first 3 and last 3 characters for masking display (security)
                if ($Integration->value === IntegrationsEnum::CRDB->value) {
                    $keyLength = strlen($key);
                    if ($keyLength >= 6) {
                        $configuration['KeyPrefix'] = substr($key, 0, 3);
                        $configuration['KeySuffix'] = substr($key, -3);
                    }
                }

                $crmIntegration = APICredential::create([
                    'Integration' => $Integration->value,
                    'Configuration' => $configuration,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($crmIntegration->refresh())->event('updated')->log('Generated ' . $Integration->description() . ' api key.');
            });
        } catch (Throwable|Exception $e) {
            Log::error('Error updating ' . $Integration->description() . ' failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'integration' => $Integration->value,
            ]);
            return $this->errored('unexpected error, try again later: ' . (config('app.debug') ? $e->getMessage() : ''));
        }

        return $this->succeeded('key generated.', data: ['token' => $key]);
    }

    private function _reportServiceConfiguration(string $Host, string $Path, string $Username, #[SensitiveParameter] string $password, User $actor): JsonResponse
    {
        $DisplayName = SSRSService::testConfig($Host, $Path, $Username, $password);
        if (is_null($DisplayName)) {
            throw ValidationException::withMessages([
                'password' => ['invalid credentials']
            ]);
        }
        return $this->_saveData(IntegrationsEnum::ReportService, [
            'host' => $Host,
            'username' => $Username,
            'path' => $Path,
            'name' => $DisplayName,
            'password' => Crypt::encryptString($password),
        ], $actor);
    }

    private function _iTrackConfiguration(string $Host, string $Username, #[SensitiveParameter] string $password, User $actor): JsonResponse
    {
        if (!iTrackService::testConfig($Host, $Username, $password)) {
            throw ValidationException::withMessages([
                'iTrack_Password' => ['invalid credentials'],
                'iTrack_Username' => ['invalid credentials']
            ]);
        }
        return $this->_saveData(IntegrationsEnum::iTrack, [
            'host' => $Host,
            'username' => $Username,
            'password' => Crypt::encryptString($password),
        ], $actor);
    }

    private function _saveOrganizationBranding(string $name, ?string $motto, ?string $logo, User $actor): JsonResponse
    {
        $path = null;
        try {
            if (is_string($logo) && str_starts_with($logo, 'data:image/')) {
                // data URL: data:image/png;base64,xxxx
                [$meta, $data] = explode(',', $logo, 2);
                $ext = 'png';
                if (preg_match('/data:image\/(\w+);base64/i', $meta, $m)) {
                    $ext = strtolower($m[1]);
                }
                $binary = base64_decode($data, true);
                if ($binary !== false) {
                    $filename = 'branding/logo_' . Str::random(12) . '.' . $ext;
                    // store publicly
                    Storage::disk('public')->put($filename, $binary);
                    // Store as relative path without domain
                    $path = 'storage/' . $filename;
                }
            } elseif (is_string($logo) && $logo !== '') {
                // If it's a full URL, extract just the path
                if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
                    $parsed = parse_url($logo);
                    $path = ltrim($parsed['path'] ?? '', '/');
                } else {
                    // Already a relative path
                    $path = $logo;
                }
            }
        } catch (Throwable $e) {
            Log::warning('Org logo store failed: ' . $e->getMessage());
        }

        $payload = [
            'name' => $name,
            'motto' => $motto,
        ];
        if ($path) {
            $payload['logo'] = $path;
        }

        return $this->_saveData(IntegrationsEnum::Organization, $payload, $actor);
    }
}
