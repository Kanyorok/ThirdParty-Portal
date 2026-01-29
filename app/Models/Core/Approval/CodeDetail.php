<?php

namespace App\Models\Core\Approval;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeDetail extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public static function getPrimaryKey(): string
    {
        return 'CodeDetailsId';
    }

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_CodeDetails';
    protected $primaryKey = 'ID';



    protected $fillable = [
        'CodeID','Value', 'Description', 'DisplayOrder', 'IsActive',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = ['DisplayOrder' => 'integer'];
}
