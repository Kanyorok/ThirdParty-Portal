<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCaseCounsel extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalCaseCounsels';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'LegalCaseID',
        'CounselName',
        'FirmName',
        'Email',
        'Phone',
        'Role',
        // 'IsExternal',
        // 'AssignedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalCaseCounselsIdId';
    }

    // Relation: A counsel belongs to one case
    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'LegalCaseID', 'Id');
    }
}
