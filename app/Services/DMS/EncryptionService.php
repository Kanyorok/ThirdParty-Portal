<?php

namespace App\Services\DMS;

use Illuminate\Encryption\Encrypter;
use SensitiveParameter;

class EncryptionService
{
    protected string $Key;
    protected string $Cypher;

    public function __construct()
    {
        $this->Cypher = config('app.cipher');
        $this->Key = base64_decode(substr(config('app.key'), 7));
    }

    public function encrypt(#[SensitiveParameter] string $content): string
    {
        return (new Encrypter($this->Key, $this->Cypher))->encryptString($content);
    }

    public function decrypt(string $crypticKey): string
    {
        return (new Encrypter($this->Key, $this->Cypher))->decryptString($crypticKey);
    }
}
