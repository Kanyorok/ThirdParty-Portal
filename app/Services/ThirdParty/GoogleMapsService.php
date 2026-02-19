<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Settings\APICredential;
use GuzzleHttp\Client;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Log;
use SensitiveParameter;
use Spatie\Geocoder\Exceptions\CouldNotGeocode;
use Spatie\Geocoder\Geocoder;
use stdClass;

class GoogleMapsService
{
    private Geocoder $geocoder;
    private string $apiKey;

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

        $this->apiKey = $api_key;
        $this->geocoder = (new Geocoder(new Client()))->setApiKey($api_key);
    }

    public static function testConfig(#[SensitiveParameter] string $api_key): bool
    {
        try {
            (new self($api_key))->geocoder->getAllCoordinatesForAddress('Craft Silicon, Nairobi');
        } catch (ErroredException | CouldNotGeocode) {
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

    public function geocodeAddress(string $address): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->apiKey,
        ]);

        if ($response->successful() && $response->json('status') === 'OK') {
            $location = $response->json('results.0.geometry.location');

            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
            ];
        }

        return null;
    }

    public function getPlaceDetails(string $placeId): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'key' => $this->apiKey,
        ]);

        if ($response->successful() && $response->json('status') === 'OK') {
            return $response->json('result');
        }

        return null;
    }

    /**
     * Calculate distance between two points
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float|int
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float | int
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1.609344;
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
