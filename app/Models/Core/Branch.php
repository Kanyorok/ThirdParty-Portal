<?php

namespace App\Models\Core;

use App\Models\Auth\User;
use App\Models\Inventory\Store;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Branches';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'UserId', 'ManagerId', 'Name', 'BranchID', 'Address', 'Address2', 'City', 'State', 'Zip', 'Country', 'Phone', 'Fax', 'Email',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'BranchID' => 'string',
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

    public function store()
    {
        return $this->hasMany(Store::class, 'BranchID', 'Id');
    }
}
