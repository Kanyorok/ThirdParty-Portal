<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThirdPartiesBankDetails extends Model
{
    use Notifiable, SoftDeletes;

    public static $snakeAttributes = false;

    protected $table = 't_ThirdPartiesBankDetails';
    protected $primaryKey = 'BankID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ThirdPartyID',
        'BankName',
        'Branch',
        'AccountNumber',
        'Currency',
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
        'ThirdPartyID' => 'integer',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class, 'ThirdPartyID', 'Id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class, 'CreatedBy', 'Id');
    }
}
