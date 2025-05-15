<?php

namespace App\Services\BR;

use App\Enums\Core\IntegrationsEnum;
use App\Enums\Employee\GenderEnum;
use App\Exceptions\ErroredException;
use App\Models\APICredential;
use App\Models\BR\Branch;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class CBSService
{
    private Client $client;
    private string $base_url, $consumer_key, $consumer_secret;

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        $cred = APICredential::query()->where('Integration', IntegrationsEnum::CoreBanking->value)->latest('Id')->first();
        if (!$cred instanceof APICredential) {
            throw new ErroredException('no core banking configuration');
        }
        $this->client = new Client([
                                    'verify'  => false,
                                    'headers' => [
                                                  'Content-Type' => 'application/json',
                                                  'Accept'       => 'application/json',
                                                 ],
                                   ]);
        $coreConfig = $cred?->Configuration;
        $this->consumer_key = $coreConfig?->ConsumerKey;
        $this->consumer_secret = $coreConfig?->ConsumerSecret;
        $this->base_url = $coreConfig?->host;
    }

    /**
     * @throws ErroredException
     */
    public function accessToken(): string
    {
        $credentials = collect([
                                "ConsumerKey"    => $this->consumer_key,
                                "ConsumerSecret" => $this->consumer_secret,
                               ]);
        try {
            $response = $this->client->post($this->base_url . '/Login', ['body' => $credentials->toJson()]);
            $data = (object) json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            return $data->accessToken;
        } catch (GuzzleException | \Exception) {
        }
        throw new ErroredException('an expected error occurred.');
    }


    public function getClientImage(string $clientID, string $Type = "P"): string
    {
        $json = $this->getClient($clientID);
        if (is_null($json)) {
            return '';
        }

        if ($json instanceof StdClass && property_exists($json, 'PhotoSignature')) {
            foreach ($json?->PhotoSignature as $Image) {
                if ($Image?->ImageType === $Type) {
                    return $Image?->Image;
                }
            }
        }

        return '';
    }

    public function getClient(string $clientID): ?object
    {
        //todo cache this
        $data = collect(['memberNo' => $clientID, "mobileNo" => "", "idNumber" => ""]);
        try {
            $response = $this->client->post($this->base_url . '/Client/SearchClient', [
                                                                                       'body'    => $data->toJson(),
                                                                                       'headers' => ['token' => $this->accessToken()],
                                                                                      ]);
        } catch (GuzzleException | \Exception $e) {
            Log::error('Fetch Client Object: ');
            Log::error($e);
            return null;
        }

        try {
            $data = (object) json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            return json_decode($data->resp['outputJSON'], false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('JSON Decode Client Object: ');
            Log::error($e);
        }
        return null;
    }

    /**
     * {
     *   {"AccountID": "", "ClientID": "275934"}
     * }
     */
    public function createClient(
        Branch $branch,
        GenderEnum $gender,
        User $actor,
        Carbon $dob,
        string $first_name,
        string $surname,
        string $memberClassId,
        string $govtId,
        string $taxID,
        string $phoneNumber,
        string $email,
        string $address1,
        string $address2,
        string $countyId,
        string $occupation
    ): ?object {
        $data = collect([
                         "ourBranchID"          => $branch->OurBranchID,
                         "memberClassID"        => $memberClassId,
                         "address1"             => $address1,
                         "address2"             => $address2,
                         "countryID"            => $countyId,
                         "mobile"               => $phoneNumber,
                         "emailID"              => $email,
                         "accountOfficerID"     => $actor->ClientID,
                         "titleID"              => "U",
                         "firstName"            => $first_name,
                         "lastName"             => $surname,
                         "genderID"             => $gender->getBRCode(),
                         "nationalityID"        => $govtId,
                         "dateOfBirth"          => $dob->format('Y-m-d'),
                         "occupation"           => $occupation,
                         "kraPin"               => $taxID,
                         "introducerClientID"   => $actor->ClientID,
                         "clientID"             => "",
                         "cityID"               => "",
                         "phone2"               => "",
                         "middleName"           => "",
                         "passportNo"           => $govtId,
                         "identificationTypeID" => "",
                         "passportIssuedCityID" => "",
                         "passportExpiryDate"   => "2024-12-10T07:04:53.742Z",
                         "accountID"            => "",
                         "clanID"               => "",
                         "ethinicGroupID"       => "",
                         "placeOfIssue"         => "",
                         "otherDetails"         => [
                                                    "mPesaReference"             => Str::random(7),
                                                    "employer"                   => "",
                                                    "monthlyDepositContribution" => "",
                                                    "remmitanceMode"             => "",
                                                   ],
                         "placeOfBirth"         => "",
                         "createdBy"            => "",
                        ]);

        try {
            $response = $this->client->post($this->base_url . '/Client/AddAccountService', [
                                                                                            'body'    => $data->toJson(),
                                                                                            'headers' => ['token' => $this->accessToken()],
                                                                                           ]);
        } catch (GuzzleException | \Exception $e) {
            Log::error('Fetch Client Object: ');
            Log::error($e);
            return null;
        }

        try {
            $data = (object) json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (($data->resp['status'] !== "000") || !is_string($data->resp['outputJSON'])) {
                throw new ErroredException(json_encode($data));
            }
            $details = json_decode($data->resp['outputJSON'], false, 512, JSON_THROW_ON_ERROR);
            return $details->Details[0];
        } catch (ErroredException $e) {
            Log::error(' Sending Lead to CBS Failed, CBS Returned with error : ' . $e->getMessage());
        } catch (\JsonException $e) {
            Log::error('JSON Decode Client Object');
            Log::error($e);
        }

        return null;
    }
}
