<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum ResponseStatusEnum: string {
    use UsefulEnumTrait;

    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined'; 

    public function label(): string{
        return match($this){
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
        };
    }
}
