<?php

namespace App\Enums;

use App\Exceptions\ErroredException;
use App\Models\Core\CodeDetail;
use App\Traits\UsefulEnumTrait;

enum TicketStatusEnum: string
{
    use UsefulEnumTrait;

    case Active = 'A';

    case Cancelled = 'C';

    case Resolved = 'R';

    case Approval = 'P';

    /**
     * @throws ErroredException
     */
    public function codeDetail(): CodeDetail
    {
        $code = CodeDetail::query()->where('CodeID', 'TicketStatus')->where('Value', $this->value)->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        throw new ErroredException('Invalid Status');
    }
}
