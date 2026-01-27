<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum RoleEnum: string
{
    use UsefulEnumTrait;

    case Read = 'r';

    case Write = 'w';

    case Share = 's';

    case Admin = 'a';

    public function description(array $instruction = null): string
    {
        $description = $this->name;
        if (empty($instruction)) {
            return $description;
        }

        //[append=>['w' =>'Write']]
        if (array_key_exists('append', $instruction)) {
            $append = $instruction['append'];
            if (array_key_exists($this->value, $append)) {
                return $description . ' (' . $append[$this->value] . ')';
            }
        }

        return $description;
    }
}
