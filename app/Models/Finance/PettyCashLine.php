<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashLine extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_PettyCashLines';
    protected $primaryKey = 'LineID';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'VoucherID', 'GLAccountID', 'Description', 'Amount',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
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
