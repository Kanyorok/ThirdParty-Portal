<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Currency;
use App\Models\Finance\BankBranch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ThirdPartiesBankDetails extends Model
{
    use Notifiable;
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_ThirdPartiesBankDetails';
    protected $primaryKey = 'BankID';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ThirdPartyId', 'CurrencyId', 'AccountNumber', 'BranchID', 'Extra',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Extra' => 'array',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'ThirdPartyId' => 'integer',
        'CurrencyId' => 'integer',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BankBranch::class, 'BranchID', 'BranchID');
    }

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartiesBankID';
    }
}
