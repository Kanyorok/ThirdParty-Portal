<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterBranchRequisition extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_InterBranchRequisition';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';

    protected $fillable = [
        'ReqNo',
        'FromBranch',
        'ToBranch',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'Status' => 'string',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'FromBranch', 'Id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'ToBranch', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function items()
    {
        return $this->hasMany(InterBranchRequisitionItem::class, 'RequisitionId', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'RequisitionId';
    }

    public function transfer()
    {
        return $this->hasOne(TransactionTransfer::class, 'RequisitionId', 'Id');
    }

    public function scopeActive($query)
    {
        $activeStatusId = cache()->rememberForever('status_active_id', function () {
            return \DB::table('t_CodeDetails')
                ->where('Code', 'Status')
                ->where('Name', 'Active')
                ->value('Id');
        });

        return $query->where('Status', $activeStatusId);
    }
}
