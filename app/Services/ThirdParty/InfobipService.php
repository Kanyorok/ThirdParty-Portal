<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\APICredential;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Infobip\Api\EmailApi;
use Infobip\ApiException;
use Infobip\Configuration;
use SensitiveParameter;

class InfobipService
{
    protected EmailApi $sendApi;
    public string $email;

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        $cred = APICredential::query()->where('Integration', IntegrationsEnum::InfoBip->value)->latest('Id')->first();
        if (!$cred instanceof APICredential) {
            throw new ErroredException('no InfoBip configuration Found');
        }

        $this->sendApi = new EmailApi(config: new Configuration(
            host:  $cred->Configuration?->Host,
            apiKey:  $cred->Configuration?->Key,
        ));
        $this->email= $cred->Configuration?->Email;
    }

    public static function testConfig(string$host, string $email, #[SensitiveParameter] string $ApiKey, User $actor):bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get($host);

            if ($response->getStatusCode() === 200 && json_decode((string)$response->getBody(), true) === 'OK') {
                (new EmailApi(config: new Configuration(
                    host: $host,
                    apiKey: $ApiKey
                )))->sendEmail(to:[$actor->Email], from: $email,subject:'Infobip Configuration Test Email',
                    html: '<p>This is a test email to confirm that the Infobip email integration has been successfully configured.</p>');


                return true;
            }
        } catch (\Exception|GuzzleException|ApiException) {
        }

        return false;
    }

    public function sendEmail(array $to, string $subject, string $body, string $campaignID=null):?string
    {
        try {
            $response = $this->sendApi->sendEmail(
                to: $to,
                from: $this->email,
                subject: $subject,
                html: $body,
                campaignReferenceId:$campaignID,
            );
            //bulkId
            //messageId
            //
            if($response instanceof \Infobip\Model\EmailSendResponse){
              return $response->getMessages()[0]?->getMessageId();
            }

           /*  {#2725
                #bulkId: "jzynyb8dokpfd45xhurk"
                #messages: array:1 [
                0 => Infobip\Model\EmailResponseDetails {#2708
                    #to: "mureithi.maina@craftsilicon.com"
                    #messageId: "ebdju3zphhmuo8mnp4cq"
                    #status: Infobip\Model\MessageStatus {#2699
                    #groupId: 1
                    #groupName: "PENDING"
                    #id: 26
                    #name: "PENDING_ACCEPTED"
                    #description: "Message accepted, pending for delivery."
                    #action: null
                }
                }
              ]
            }*/
        } catch (ApiException $e) {
            Log::error('Sending InfoBip Email:');
            Log::error($e);
        }

        return null;
    }
}
