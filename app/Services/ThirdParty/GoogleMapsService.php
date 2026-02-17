<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Settings\APICredential;
use GuzzleHttp\Client;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Log;
use SensitiveParameter;
use Spatie\Geocoder\Exceptions\CouldNotGeocode;
use Spatie\Geocoder\Geocoder;
use stdClass;

class GoogleMapsService
{
    private Geocoder $geocoder;

    /**
     * @throws ErroredException
     */
    public function __construct(#[SensitiveParameter] string $api_key = null)
    {
        if (is_null($api_key)) {
            $configs = APICredential::query()->where('Integration', IntegrationsEnum::GoogleMaps->value)->latest('Id')->first();
            if (! $configs instanceof APICredential) {
                throw new ErroredException('there are no google maps configuration');
            }

            $config = $configs?->Configuration;
            if (! $config instanceof stdClass) {
                throw new ErroredException('there are no google maps configuration');
            }
            if (! property_exists($config, 'key') || empty($config->key)) {
                throw new ErroredException('invalid report service configuration');
            }

            try {
                $value = Crypt::decryptString($config->key);
            } catch (DecryptException) {
                throw new ErroredException('invalid iTrack configuration.');
            }
            if ($value === false) {
                throw new ErroredException('invalid iTrack configuration.');
            }
            $api_key = $value;
        }

        $this->geocoder = (new Geocoder(new Client()))->setApiKey($api_key);

    }

    public static function testConfig(#[SensitiveParameter] string $api_key): bool
    {
        try {
            (new self($api_key))->geocoder->getAllCoordinatesForAddress('Craft Silicon, Nairobi');
        } catch (ErroredException|CouldNotGeocode) {
            return false;
        }
        return true;
    }

    public function getCoordinates(string $address): array
    {
        try {
            return $this->geocoder->getAllCoordinatesForAddress($address);
        } catch (CouldNotGeocode $e) {
            Log::error('Could not geocode address: ' . $address, ['exception' => $e]);

            return [];
        }
    }

    public function getAddress(float $latitude, float $longitude): array
    {
        try {
            return $this->geocoder->getAllAddressesForCoordinates($latitude, $longitude);
        } catch (CouldNotGeocode $e) {
            Log::error('Could not reverse geocode coordinates: ' . $latitude . ', ' . $longitude, ['exception' => $e]);

            return [];
        }
    }
}
