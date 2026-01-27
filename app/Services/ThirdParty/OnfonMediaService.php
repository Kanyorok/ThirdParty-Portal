<?php

namespace App\Services\ThirdParty;

use App\Exceptions\ErroredException;
use App\Models\Communication\SMS;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;
use SensitiveParameter;

/**
 * username = client_id
 */
class OnfonMediaService
{
    protected Client $client;
    protected string $key;
    protected string $client_id;
    protected string $sender_id;

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        throw new ErroredException('Not Implemented');
        /* $cred = APICredential::query()->where('Integration', IntegrationsEnum::SMS->value)->latest('Id')->first();
         if (!$cred instanceof APICredential) {
             throw new ErroredException('no sms configuration');
         }
         $this->client = self::_getClient();
         $smsConfig = $cred?->Configuration;
         $this->key = $smsConfig?->key;
         $this->sender_id = $smsConfig?->sender_Id;
         $this->client_id = $smsConfig?->client_id;*/
    }

    protected static function _getClient(): Client
    {
        return new Client([
                           'base_uri' => "https://api.onfonmedia.co.ke/v1/sms/",
                           'headers' => ['Accept' => 'application/json'],
                          ]);
    }

    public static function testConfig(string $ClientId, #[SensitiveParameter] string $ApiKey): bool
    {
        try {
            $response = self::_getClient()->get('Balance', [
                                                            'query' => [
                                                                        'ApiKey' => $ApiKey,
                                                                        'ClientId' => $ClientId,
                                                                       ],
                                                           ]);
            $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

            return ($data->ErrorCode === 0);
        } catch (GuzzleException | JsonException | \Exception) {
        }

        return false;
    }

    /**
     * Get Balance.
     *
     * @throws ErroredException
     */
    public function balance(): array
    {
        try {
            $response = $this->client->get('Balance', [
                                                       'query' => [
                                                                   'ApiKey' => $this->key,
                                                                   'ClientId' => $this->client_id,
                                                                  ],
                                                      ]);
            $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
            if ($data->ErrorCode === 0) {
                return [
                        'balance' => (float) $data->Data[0]->Credits,
                        'currency' => $data->Data[0]->PluginType,
                        'dated' => Carbon::now()->format('Y-m-d H:i:s'),
                       ];
            }
        } catch (GuzzleException | JsonException | \Exception) {
        }

        throw new ErroredException("Could Not Fetch Balance");
    }

    public function sendMessage(SMS $sms): bool
    {
        try {
            $response = $this->client->post('SendBulkSMS', [
                                                            'json' => [
                                                                       'ApiKey' => $this->key,
                                                                       'ClientId' => $this->client_id,
                                                                       'SenderId' => $this->sender_id,
                                                                       'MessageParameters' => [
                                                                                               [
                                                                                                'Number' => $this->formatKenyaCode($sms->to),
                                                                                                'Text' => $sms->Content,
                                                                                               ],
                                                                                              ],
                                                                      ],
                                                           ]);

            //{"ErrorCode": 0, "ErrorDescription": "null", "Data": [{"MessageErrorCode": 0, "MessageErrorDescription": "Success", "MobileNumber": "254717861596", "MessageId": "5c358ff1-bb38-49dd-bb70-410ba99dc09e", "Custom": ""}]}
            $json_response = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

            if ($json_response->ErrorCode === 0) {
                $data = $json_response->Data[0];
                switch ($data->MessageErrorCode) {
                    case 0://success
                        return true;
                    case 401://credentials
                        $reason = 'credentials';

                        break;
                    case 412://No route found
                        $reason = 'Phone number issues';

                        break;
                    default:
                        $reason = 'Unknown issue';
                }
                Log::error('Sending sms (' . $sms->Id . ') Failed : ' . $reason);

                return false;
            }
            Log::error('Sending sms (' . $sms->Id . ') Failed : Credentials : possibly access key');
        } catch (GuzzleException  $exception) {
            Log::error('Guzzle onfon error' . $exception->getMessage());
        } catch (JsonException $e) {
            Log::error('Json encode onfon error' . $e->getMessage());
        } catch (ErroredException $e) {
            Log::error('Phone number issue onfon error' . $e->getMessage());
        }

        return false;
    }

    /**
     * @throws ErroredException
     */
    protected function formatKenyaCode(string $phone_number): string
    {
        if (strlen((int) $phone_number) === 9) {
            if (! $phone_number) {
                throw new ErroredException("Invalid phone number given ! ");
            }

            return "+254" . ltrim($phone_number, 0);
        }

        if (strlen($phone_number) === 10) {
            $number = substr($phone_number, -9);
            if (! $number) {
                throw new ErroredException("Invalid phone number given ! ");
            }

            return "+254" . ltrim($number, 0);
        }

        if (strlen($phone_number) === 13 && (str_starts_with($phone_number, '+254'))) {
            return $phone_number;
        }

        throw new ErroredException("Invalid phone number given ! ");
    }
}
