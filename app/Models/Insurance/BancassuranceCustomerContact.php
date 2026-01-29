<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceCustomerContact extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_BancassuranceCustomersContacts';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CustomerID',
        'ContactDate',
        'ContactType',
        'Summary',
        'HandledBy',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BancassuranceCustomersContactsId';
    }

    public function contacttypes()
    {
        return $this->belongsTo(CodeDetail::class, 'ContactType', 'ID');
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'HandledBy', 'Id');
    }

    public function customers()
    {
        return $this->belongsTo(BancassuranceCustomer::class, 'CustomerID', 'Id');
    }
}
