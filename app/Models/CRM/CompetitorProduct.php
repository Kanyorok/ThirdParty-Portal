<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitorProduct extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_CompetitorProducts';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'CompetitorProductsId';
    }
    protected $fillable = [
                           "CompetitorId",
                           "Name",
                           "Limit",
                           "InterestRate",
                           "OtherCharges",
                           "RepaymentPeriod",
                           "SecurityRequired",
                           "Clients",
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];
}
