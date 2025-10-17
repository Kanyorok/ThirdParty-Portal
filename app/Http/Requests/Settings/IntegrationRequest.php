<?php

namespace App\Http\Requests\Settings;

use App\Enums\Core\IntegrationsEnum;
use App\Enums\EmailEncryptionEnum;
use App\Exceptions\ErroredException;
use App\Rules\isDomain;
use EchoLabs\Prism\Enums\Provider;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IntegrationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Integration' => ['required', Rule::enum(IntegrationsEnum::class),],

            // SSRS Integration Validation Rules
            'SSRS_Host' => ['exclude_unless:Integration,' . IntegrationsEnum::ReportService->value, 'required', 'string', 'url:http,https', 'max:200'],
            'SSRS_Path' => ['exclude_unless:Integration,' . IntegrationsEnum::ReportService->value, 'required', 'string', 'max:200'],
            'SSRS_Username' => ['exclude_unless:Integration,' . IntegrationsEnum::ReportService->value, 'required', 'string', 'max:200'],
            'SSRS_Password' => ['exclude_unless:Integration,' . IntegrationsEnum::ReportService->value, 'required', 'string', 'max:200'],

            // Email Integration Validation Rules
            'Incoming_Server' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'string', new isDomain()],
            'Incoming_Port' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'integer', 'min:1', 'max:65535'],
            'Incoming_Encryption' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', Rule::enum(EmailEncryptionEnum::class)],
            'Incoming_Username' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'string', 'max:200'],
            'Incoming_Password' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'string', 'max:200'],
            'Outgoing_Server' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'string', new isDomain()],
            'Outgoing_Port' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'integer', 'min:1', 'max:65535'],
            'Outgoing_Encryption' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', Rule::enum(EmailEncryptionEnum::class)],
            'Outgoing_Username' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'string', 'max:200'],
            'Outgoing_Password' => ['exclude_unless:Integration,' . IntegrationsEnum::Email->value, 'required', 'string', 'max:200'],

            // SMS Integration Validation Rules
            'SMS_Priority' => ['exclude_unless:Integration,' . IntegrationsEnum::SMS->value, 'required', 'string', 'max:100'],
            'SMS_Message_Type' => ['exclude_unless:Integration,' . IntegrationsEnum::SMS->value, 'required', 'string', 'max:100'],
            'SMS_Sender_Id' => ['exclude_unless:Integration,' . IntegrationsEnum::SMS->value, 'required', 'string', 'max:200'],
            'SMS_Password' => ['exclude_unless:Integration,' . IntegrationsEnum::SMS->value, 'required', 'string', 'max:200'],

            // Facebook Integration Validation Rules
            'FB_Page_Id' => ['exclude_unless:Integration,' . IntegrationsEnum::Facebook->value, 'required', 'numeric'],
            'FB_App_Id' => ['exclude_unless:Integration,' . IntegrationsEnum::Facebook->value, 'required', 'numeric'],
            'FB_App_Secret' => ['exclude_unless:Integration,' . IntegrationsEnum::Facebook->value, 'required', 'string', 'max:200'],
            'FB_Page_Token' => ['exclude_unless:Integration,' . IntegrationsEnum::Facebook->value, 'required', 'string', 'max:5000'],

            // Twitter X Integration Validation Rules
            'X_Access_Token' => ['exclude_unless:Integration,' . IntegrationsEnum::Twitter->value, 'required', 'string', 'max:200'],
            'X_Access_Token_Secret' => ['exclude_unless:Integration,' . IntegrationsEnum::Twitter->value, 'required', 'string', 'max:200'],
            'X_Consumer_Key' => ['exclude_unless:Integration,' . IntegrationsEnum::Twitter->value, 'required', 'string', 'max:200'],
            'X_Consumer_Secret' => ['exclude_unless:Integration,' . IntegrationsEnum::Twitter->value, 'required', 'string', 'max:200'],
            'X_Bearer_Token' => ['exclude_unless:Integration,' . IntegrationsEnum::Twitter->value, 'required', 'string', 'max:500'],
            'X_Is_Free' => ['exclude_unless:Integration,' . IntegrationsEnum::Twitter->value, 'required', Rule::in(['yes', 'no'])],

            // Channels Integration Validation Rules
            'Channel_Callback' => ['exclude_unless:Integration,' . IntegrationsEnum::Channels->value, 'required', 'string', 'url:http,https', 'active_url', 'max:200'],

            // Artificial Intelligence Integration Validation Rules
            'LLM_API_Key' => ['exclude_unless:Integration,' . IntegrationsEnum::LLM->value, 'required', 'string'],
            'LLM_Provider' => ['exclude_unless:Integration,' . IntegrationsEnum::LLM->value, 'required', 'string', Rule::enum(Provider::class)],
            'LLM_Model' => ['exclude_unless:Integration,' . IntegrationsEnum::LLM->value, 'required', 'string'],

            // Core Banking Integration Validation Rules
            'CBS_Host' => ['exclude_unless:Integration,' . IntegrationsEnum::CoreBanking->value, 'required', 'string', 'url:http,https', 'max:200'],
            'CBS_ConsumerKey' => ['exclude_unless:Integration,' . IntegrationsEnum::CoreBanking->value, 'required', 'string', 'max:200'],
            'CBS_ConsumerSecret' => ['exclude_unless:Integration,' . IntegrationsEnum::CoreBanking->value, 'required', 'string', 'max:200'],

            // InfoBip Integration Validation Rules
            'InfoBip_Host' => ['exclude_unless:Integration,' . IntegrationsEnum::InfoBip->value, 'required', 'string', 'max:200'],
            'InfoBip_Email' => ['exclude_unless:Integration,' . IntegrationsEnum::InfoBip->value, 'required', 'string', 'max:200', 'email:rfc,dns'],
            'InfoBip_API_Key' => ['exclude_unless:Integration,' . IntegrationsEnum::InfoBip->value, 'required', 'string'],
        ];
    }

    /**
     * @throws ErroredException
     */
    public function getIntegration(): IntegrationsEnum
    {
        try {
            return IntegrationsEnum::fromValue($this->validated('Integration'));
        } catch (ErroredException) {
        }
        throw new ErroredException('unknown integration given');
    }

    /**
     * @throws ValidationException
     */
    public function getAIProvider(): Provider
    {
        try {
            return Provider::from($this->validated('LLM_Provider'));
        } catch (Exception|Throwable) {
        }

        throw ValidationException::withMessages(['LLM_Provider' => 'unknown provider given']);
    }

    /**
     * @throws ValidationException
     */
    public function getOutgoingEncryption(): EmailEncryptionEnum
    {
        try {
            return EmailEncryptionEnum::fromValue($this->validated('Outgoing_Encryption'));
        } catch (Exception|Throwable) {
        }

        throw ValidationException::withMessages(['Outgoing_Encryption' => 'unknown encryption given']);
    }

    /**
     * @throws ValidationException
     */
    public function getIncomingEncryption(): EmailEncryptionEnum
    {
        try {
            return EmailEncryptionEnum::fromValue($this->validated('Incoming_Encryption'));
        } catch (Exception|Throwable) {
        }

        throw ValidationException::withMessages(['Incoming_Encryption' => 'unknown encryption given']);
    }

    /**
     * @throws ValidationException
     */
    public function getChannelCallback(): string
    {
        try {
            if (Http::get($this->validated('Channel_Callback'))->successful()) {
                return $this->validated('Channel_Callback');
            }
        } catch (Exception|Throwable) {
        }

        throw ValidationException::withMessages(['Channel_Callback' => 'url given may not not reachable']);
    }

    /**
     * @throws ValidationException
     */
    public function getSSRS_Host(): string
    {
        try {
            Http::get($this->string('SSRS_Host')->trim()->toString());
        } catch (ConnectionException $e) {
            throw ValidationException::withMessages(['SSRS_Host' => 'url given may not not reachable']);
        }

        return $this->validated('SSRS_Host');
    }


    public function getCBSHost(): string
    {
        $credentials = collect([
            "ConsumerKey" => $this->validated('CBS_ConsumerKey'),
            "ConsumerSecret" => $this->validated('CBS_ConsumerSecret'),
        ]);
        $client = new Client([
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        try {
            if ($client->post($this->validated('CBS_Host') . '/Login', ['body' => $credentials->toJson()])->getStatusCode() === Response::HTTP_OK) {
                return $this->validated('CBS_Host');
            }
        } catch (GuzzleException|Exception) {
        }
        throw ValidationException::withMessages([
            'CBS_Host' => 'host or credentials may be invalid ',
            'CBS_ConsumerKey' => 'key may be invalid',
            'CBS_ConsumerSecret' => 'secret may be invalid',
        ]);
    }
}
