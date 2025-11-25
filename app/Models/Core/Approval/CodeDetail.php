<?php

namespace App\Models\Core\Approval;

use App\Traits\Model\UserActorTrait;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeDetail extends Model
{
    use SoftDeletes, UserActorTrait;

      public static function getPrimaryKey(): string
    {
        return 'CodeDetailsId';
    }

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_CodeDetails';
    protected $primaryKey = 'ID';

  

    protected $fillable = [
        'CodeID','Value', 'Description', 'DisplayOrder', 'IsActive',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = ['DisplayOrder' => 'integer'];

    
}
