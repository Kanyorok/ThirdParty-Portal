<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmBranch extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_CRMBranches';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'UserId', 'ManagerId', 'Name', 'BranchID', 'Address', 'Address2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Fax', 'Email',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BranchID';
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserId', 'Id')->withTrashed();
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ManagerId', 'Id')->withTrashed();
    }
}
