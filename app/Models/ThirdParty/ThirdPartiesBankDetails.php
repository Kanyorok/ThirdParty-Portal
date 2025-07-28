<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Core\Currency;
use App\Models\ThirdParty\ThirdPartyUser;

class ThirdPartiesBankDetails extends Model
{
    use Notifiable, SoftDeletes;

    protected $table = 't_ThirdPartiesBankDetails';
    protected $primaryKey = 'BankID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ThirdPartyId',
        'BankName',
        'Branch',
        'AccountNumber',
        'CurrencyId',
        'SwiftCode',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyUser::class, 'CreatedBy', 'Id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }
}
