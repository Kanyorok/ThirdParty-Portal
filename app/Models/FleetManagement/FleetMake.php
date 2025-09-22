<?php

namespace App\Models\FleetManagement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;


class FleetMake extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $table = 't_FleetBrands';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [

        'BrandID',
        'BrandName',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];


    public static function getPrimaryKey(): string
    {
        return 'BrandId';
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


}
