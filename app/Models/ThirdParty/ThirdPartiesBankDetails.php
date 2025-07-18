<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Core\Currency;

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

    /**
     * Get the third party associated with the bank detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    /**
     * Get the creator of the bank detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'CreatedBy', 'Id');
    }
    /**
     * Accepted currency for the bank detail?
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'Id');
    }
}
