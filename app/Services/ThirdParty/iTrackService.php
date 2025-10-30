<?php

namespace App\Services\ThirdParty;

use GuzzleHttp\Client;
use SensitiveParameter;

class iTrackService
{

    public function __construct()
    {
    }

    /*public function getTrack(#[SensitiveParameter] string $password, string $username, array $imeis): array
    {
        $token = $this->_getToken($password, $username);

        $client = new Client();
        try {
            $response = $client->get(self::path.'/api/track', [
                'query' => [
                    'access_token' => $token,
                    'imeis' => implode(',', $imeis)
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            \Log::error('iTrack Track api failed ! ' . $e->getMessage());
            throw new ErroredException('Track api failed !');
        } catch (\JsonException $e) {
            \Log::error('iTrack Decode track json failed ! ' . $e->getMessage());
            throw new ErroredException('Decode track json failed !');
        }
    }*/
    public function getToken(#[SensitiveParameter] string $password, string $username)
    {
        $timestamp = time();
        $signature = md5(md5($password) . $timestamp);

        $client = new Client();
        $response = $client->get('https://www.itrack.top/api/authorization', [
            'query' => [
                'time' => $timestamp,
                'account' => $username,
                'signature' => $signature
            ]
        ]);

        http://api.itrack.top/api/authorization?time=1504084954&account=test&signature=ecca11e8e116455c48c8ad2ef5c1e0ee the password=123456，md5(md5(123456) + 1504084954)

        return json_decode($response->getBody()->getContents(), true);
    }
}
