<?php

namespace App\Models;

use App\Enums\InvitationResponseStatus;
use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderInvitation extends Model
{
    use SoftDeletes;

    protected $table = 't_TenderInvitations';
    protected $primaryKey = 'InvitationID';
    protected $keyType = 'integer';
    public $incrementing = true;

    protected $fillable = [
        'TenderID',
        'SupplierID',
        'InvitationDate',
        'ResponseStatus',
        'ResponseDate',
        'DeclineReason',
        'ConfirmationAttachmentPath',
    ];

    protected $casts = [
        'InvitationDate' => 'date:Y-m-d',
        'ResponseDate' => 'date:Y-m-d',
        'ResponseStatus' => InvitationStatusEnum::class,
    ];

    public function tender(): BelongsTo {
        return $this->belongsTo(Tender::class, 'TenderID');
    }

    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class, 'Id');
    }

    public function isAccepted(): bool {
        return $this->ResponseStatus === ResponseStatusEnum::Accepted;
    }

    public function isDeclined(): bool {
        return $this->ResponseStatus === ResponseStatusEnum::Declined;
    }

    public function isPending(): bool {
        return $this->ResponseStatus === ResponseStatusEnum::Pending;
    }
}
