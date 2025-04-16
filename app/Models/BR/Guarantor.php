<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guarantor extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedDate';
    public $incrementing = false;
    public $timestamps = false;
    //protected $connection = 'brcbs';
    protected $connection = 'sqlsrv';
    protected $keyType = 'string';
    protected $table = 'syn_t_AccountGuarantor';
    protected $primaryKey = null;

    protected $casts = [
                        'GuaranteeAmount' => 'decimal:2',
                        'CreatedOn'       => 'datetime',
                       ];

    public static function primaryKey(): string
    {
        return 'GuarantorID';
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(DebtProduct::class, 'AccountID', 'AccountID');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'GuarantorID', 'ClientID');
    }
}
