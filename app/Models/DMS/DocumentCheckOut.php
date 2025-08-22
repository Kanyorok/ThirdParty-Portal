<?php

namespace App\Models\DMS;

use App\Enums\DMS\DocumentCheckOutStatusEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCheckOut extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentCheckOuts';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "DocumentId", "CheckOutRemark", "CheckInRemark", "Status", "Dated",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'DocumentId' => 'integer',
        'CreatedBy' => 'integer',
        'Dated' => 'datetime',
        'Status' => DocumentCheckOutStatusEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentCheckOutId';
    }
}
