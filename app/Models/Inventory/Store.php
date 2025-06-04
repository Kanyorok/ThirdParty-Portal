<?php

namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Branch;
use App\Models\User;

class Store extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_Stores';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'StoreID',
                           'StoreName',
                           'BranchID',
                           'Status',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                           'CreatedOn',
                           'ModifiedOn',
                          ];

    protected $casts = [
                            'StoreID'       => 'string',
                            'StoreName'       => 'string',
                            'BranchID'      => 'integer',
                            'Status'      => 'string',
                            'CreatedBy'     => 'integer',
                            'ModifiedBy'    => 'integer',
                            'DeletedBy'     => 'integer',
                            'CreatedOn'     => 'datetime',
                            'ModifiedOn'    => 'datetime',

                        ];

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
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }       
    
}