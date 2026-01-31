<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Settings\APICredential;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use SensitiveParameter;
use stdClass;

class iTrackService
{
    private string $_baseUrl;
    private string $_password;
    private string $_username;

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        $iTrack = APICredential::query()->where('Integration', IntegrationsEnum::iTrack->value)->latest('Id')->first();
        if (! $iTrack instanceof APICredential) {
            throw new ErroredException('there are no iTrack configuration.');
        }

        $config = $iTrack?->Configuration;
        if (! $config instanceof stdClass) {
            throw new ErroredException('invalid iTrack configuration.');
        }

        if (! property_exists($config, 'password') || ! property_exists($config, 'username') || ! property_exists($config, 'host')) {
            throw new ErroredException('invalid iTrack configuration.');
        }

        try {
            $password = Crypt::decryptString($config->password);
        } catch (DecryptException) {
            throw new ErroredException('invalid iTrack configuration.');
        }
        $this->_username = $config->username;
        $this->_password = (string)$password;
        $this->_baseUrl = Str::rtrim($config->host, '/');
    }

    public static function testConfig(string $url, string $username, #[SensitiveParameter] string $password): bool
    {
        try {
            return (is_string(self::_token($url, $username, $password)));
        } catch (ErroredException $e) {
            return false;
        }
    }

    /**
     * @throws ErroredException
     */
    protected static function _token(string $url, string $username, #[SensitiveParameter] string $password): string
    {
        $timestamp = time();
        $signature = md5(md5($password) . $timestamp);

        try {
            $client = new Client(['base_uri' => $url]);
            $response = $client->get('api/authorization', [
                'query' => [
                    'time' => $timestamp,
                    'account' => $username,
                    'signature' => $signature,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            Log::error('iTrack Get Token failed !' . $e->getMessage());

            throw new ErroredException('Get Token failed !');
        } catch (JsonException $e) {
            throw new ErroredException('Decode token json failed !');
        }

        if (isset($data['code']) && $data['code'] === 0) {
            return $data['record']['access_token'];
        }
        Log::error('iTrack Get Token failed !' . implode(',', $data));

        throw new ErroredException('Get Token failed !');
    }

    public function validateIMEI(int $imei): bool
    {
        try {
            $this->findTrack($imei);

            return true;
        } catch (ErroredException) {
        }

        return false;
    }

    /**
     * @throws ErroredException
     */
    public function findTrack(int $imei): Collection
    {
        return collect($this->_findTrack([$imei])[0]);
    }

    /**
     * @throws ErroredException
     */
    protected function _findTrack(array $imeis): array
    {
        $token = self::_token($this->_baseUrl, $this->_username, $this->_password);

        $client = new Client([
            'base_uri' => $this->_baseUrl,
        ]);

        try {
            $response = $client->get('/api/track', [
                'query' => [
                    'access_token' => $token,
                    'imeis' => implode(',', $imeis),
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            /*[
                "chargestatus" => 1 "fuel" => ""
                "latitude" => -0.727465 "battery" => -1 "speed" => 0 "hearttime" => 1761909238  "temperature" => []   "course" => 170
                "temperaturetime" => 0  "acctime" => 3350  "systemtime" => 1761905918  "longitude" => 37.151119  "oilpowerstatus" => -1
                "mileage" => -1 "todaymileage" => -1  "odometer" => -1  "externalpower" => ""   "servertime" => 1761909338  "accstatus" => 0
                "datastatus" => 2  "fueltime" => 0  "doorstatus" => -1   "imei" => "868003036564768" "gpstime" => 1761909086 "defencestatus" => 0
            ]*/
        } catch (GuzzleException $e) {
            \Log::error('iTrack Track api failed ! ' . $e->getMessage());

            throw new ErroredException('Track api failed !');
        } catch (JsonException $e) {
            \Log::error('iTrack Decode track json failed ! ' . $e->getMessage());

            throw new ErroredException('Decode track json failed !');
        }

        if (isset($data['code']) && $data['code'] === 0) {
            return $data['record'];
        }

        Log::error('iTrack Track failed !' . implode(',', $data));

        throw new ErroredException('Track failed !');
    }

    /**
     * @throws ErroredException
     */
    public function findMultipleTrack(array|string $imeis): Collection
    {
        $devices = is_array($imeis) ? $imeis : explode(',', $imeis);

        return collect($this->_findTrack($devices));
    }

    /**
     * Get historical tracking data for a device
     * @throws ErroredException
     */
    public function getPlayback(int $imei, Carbon $beginTime, Carbon $endTime): Collection
    {
        if ($beginTime->lt($endTime)) {
            $start = $beginTime->format('U');
            $end = $endTime->format('U');
        } else {
            $start = (int)$endTime->format('U');
            $end = (int)$beginTime->format('U');
        }

        $token = self::_token($this->_baseUrl, $this->_username, $this->_password);

        try {
            $client = new Client(['base_uri' => $this->_baseUrl]);
            $response = $client->get('/api/playback', [
                'query' => [
                    'access_token' => $token,
                    'imei' => $imei,
                    'begintime' => $start,
                    'endtime' => $end,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            Log::error('iTrack Playback api failed ! ' . $e->getMessage());

            throw new ErroredException('Playback api failed !');
        } catch (JsonException $e) {
            Log::error('iTrack Decode playback json failed ! ' . $e->getMessage());

            throw new ErroredException('Decode playback json failed !');
        }

        if (isset($data['code']) && $data['code'] === 0) {
            return collect($data['record']);
        }

        Log::error('iTrack Playback failed !' . implode(',', $data));

        throw new ErroredException('Playback failed !');
    }
}
