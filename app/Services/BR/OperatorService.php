<?php

namespace App\Services\BR;

use App\Models\BR\BRUser;
use App\Models\BR\Client;

class OperatorService
{
    private ?Client $client;
    private ?BRUser $operator;

    public function __construct(public string $OperatorID)
    {
        $this->setClient();
    }

    private function setClient(): void
    {
        if (!isset($this->client)) {
            $user = BRUser::query()->where('OperatorID', $this->OperatorID)->first();
            if ($user instanceof BRUser) {
                $this->operator = $user;
                $client = $user->client;
                if ($client instanceof Client) {
                    $this->client = $client;
                    return;
                }
                $this->client = null;
                return;
            }
            $this->client = null;
            $this->operator = null;
        }
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getOperator(): ?BRUser
    {
        return $this->operator;
    }

    public function getImage(string $attr = '', bool $placeholder = true): string
    {
        if ($this->client instanceof Client) {
            return $this->client->getImage($attr, $placeholder);
        }
        return ($placeholder)
            ? '<img src="https://placehold.co/200x200?font=roboto&text=No+Image" ' . $attr . '/>'
            : '';
    }
}
