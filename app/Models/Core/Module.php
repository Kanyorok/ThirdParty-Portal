<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Modules';
    protected $primaryKey = 'ModuleID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ModuleID', 'Name', 'Description', 'ParentID',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];


    public static function getPrimaryKey(): string
    {
        return 'ModuleID';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'ParentID', 'ModuleID');
    }

    public function children(): BelongsTo
    {
        return $this->hasMany(__CLASS__, 'ParentID', 'ModuleID');
    }


}
