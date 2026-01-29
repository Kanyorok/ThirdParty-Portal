<?php

namespace App\Models\Core;

use App\Traits\Model\RelatedPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use RelatedPermissionTrait;

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    public const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Reports';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'Description', 'Path', 'ModuleId', 'ProcedureName', 'PermissionName',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'ModuleId' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ReportId';
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'ModuleId', 'ModuleID');
    }

    public function permissionColum(): string
    {
        return 'PermissionName';
    }
}
