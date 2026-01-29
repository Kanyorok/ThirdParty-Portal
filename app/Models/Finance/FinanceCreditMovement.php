<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Credit Movement Model
 *
 * Amount field uses signed values:
 * - Positive amounts = increase available credit (payments, adjustments up, initial setup)
 * - Negative amounts = decrease available credit (invoice usage, adjustments down)
 *
 * This simplifies calculations: SUM(Amount) gives net credit change
 */
class FinanceCreditMovement extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_FinanceCreditMovements';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CreditID',
        'CustomerID',
        'MovementType',
        'Amount',
        'ReferenceType',
        'ReferenceID', 'Notes',
        'EffectiveOn',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'EffectiveOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceCreditMovementId';
    }

    // Relationships
    public function creditProfile()
    {
        return $this->belongsTo(FinanceCreditManagement::class, 'CreditID', 'Id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\ThirdParty\ThirdParties::class, 'CustomerID', 'Id');
    }

    public function creditAdjustment()
    {
        return $this->belongsTo(FinanceCreditAdjustment::class, 'ReferenceID', 'Id')
            ->where('ReferenceType', 'credit_adjustment');
    }
}
