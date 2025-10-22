<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Communication\SMS;
use App\Models\Settings\APICredential;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use SensitiveParameter;

class CSSMSService
{
    protected Client $client;
    protected string $priority, $messageType, $sender_id;
    protected string $username, $password;

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        $cred = APICredential::query()->where('Integration', IntegrationsEnum::SMS->value)->latest('Id')->first();
        if (!$cred instanceof APICredential) {
            throw new ErroredException('no sms configuration');
        }
        $this->client = self::_getClient();
        $smsConfig = $cred?->Configuration;
        $this->priority = $smsConfig?->priority;
        $this->messageType = $smsConfig?->messageType;
        $this->sender_id = $smsConfig?->sender_Id;
        $this->password = $smsConfig?->password;
    }

    protected static function _getClient(): Client
    {
        return new Client([
            'base_uri' => "http://172.17.20.27:51107/SMSServiceAPI/api/",
            'headers' => ['Accept' => 'application/json'],
        ]);
    }

    public static function testConfig(string $priority, string $messageType, #[SensitiveParameter] string $sender_id, #[SensitiveParameter] string $password, User $actor): bool
    {
        try {
            $response = self::_getClient()->post('smsservice', [
                'json' => [
                    'MessageText' => 'This is a test message after change in config',
                    'Msisdn' => self::formatKenyaCode($actor->Phone),
                    "Priority" => $priority,
                    "MessageType" => $messageType,
                    "UserID" => $sender_id,
                    "PassWD" => $password,
                    "UniqueID" => Str::uuid()->toString(),
                ],
            ]);

            $json_response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            return is_array($json_response) && array_key_exists('ResponseCode', $json_response) && $json_response['ResponseCode'] === '000';
        } catch (GuzzleException|JsonException|Exception|ErroredException) {
        }
        return false;
    }

    /**
     * @throws ErroredException
     */
    protected static function formatKenyaCode(string $phone_number): string
    {
        if (strlen((int)$phone_number) === 9) {
            if (!$phone_number) {
                throw new ErroredException("Invalid phone number given ! ");
            }
            return "254" . ltrim($phone_number, 0);
        }

        if (strlen($phone_number) === 10) {
            $number = substr($phone_number, -9);
            if (!$number) {
                throw new ErroredException("Invalid phone number given ! ");
            }
            return "254" . ltrim($number, 0);
        }

        if (strlen($phone_number) === 12 && (str_starts_with($phone_number, '254'))) {
            return $phone_number;
        }

        if (strlen($phone_number) === 13 && (str_starts_with($phone_number, '+254'))) {
            return ltrim($phone_number, '+');
        }

        throw new ErroredException("Invalid phone number given ! ");
    }

    public function sendMessage(SMS $sms): bool
    {
        try {
            $response = $this->client->post('smsservice', [
                'json' => [
                    'MessageText' => Str::of($sms->Content)->remove(["\r", "\n", "\t", "\0", "\x0B"])->replace("\u{A0}", " ")->toString(),
                    'Msisdn' => "254706249023",//self::formatKenyaCode($sms->Phone),
                    "Priority" => $this->priority,
                    "MessageType" => $this->messageType,
                    "UserID" => $this->sender_id,
                    "PassWD" => $this->password,
                    "UniqueID" => $sms->SMSId,
                ],
            ]);

            $json_response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            if (is_array($json_response)) {
                $sms->update(['Response' => $json_response]);
                if (array_key_exists('ResponseCode', $json_response) && $json_response['ResponseCode'] === '000') {
                    return true;
                }
                $reason = 'Response: ' . $json_response['ResponseCode'] . ' ';
                $reason .= array_key_exists('ResponseDescription', $json_response) ? $json_response['ResponseDescription'] : '';
                $reason .= ' ';
                $reason .= array_key_exists('Msisdn', $json_response) ? $json_response['Msisdn'] : '';

                Log::error('Sending sms (' . '$sms->Id' . ') Failed : ' . $reason);
                return false;
            }
            //{"ExternalReference":"1","Msisdn":"+254718319224","ResponseCode":"000","ResponseDescription":"MESSAGE DELIVERED"}
            Log::error('Sending sms (' . '$sms->Id' . ') Failed : unknown reason');
            return false;
        } catch (GuzzleException  $exception) {
            Log::error('Guzzle cs sms error' . $exception->getMessage());
        } catch (JsonException $e) {
            Log::error('Json encode cs error' . $e->getMessage());
        } catch (ErroredException $e) {
            Log::error('Phone number issue cs error' . $e->getMessage());
        }
        return false;
    }
}
