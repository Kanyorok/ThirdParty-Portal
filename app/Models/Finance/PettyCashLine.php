<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashLine extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_PettyCashLines';
    protected $primaryKey = 'LineID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'VoucherID', 'GLAccountID', 'Description', 'Amount',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
    ];

    public function voucher()
    {
        return $this->belongsTo(PettyCashVoucher::class, 'VoucherID', 'VoucherID');
    }

    public static function getPrimaryKey(): string
    {
        return 'LineID';
    }
}
