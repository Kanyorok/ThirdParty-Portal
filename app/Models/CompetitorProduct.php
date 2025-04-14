<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitorProduct extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_CompetitorProducts';
    protected $primaryKey = 'Id';

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
