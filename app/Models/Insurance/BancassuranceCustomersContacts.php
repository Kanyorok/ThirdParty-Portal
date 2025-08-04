<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Insurance\BancassuranceCustomers;
use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;

class BancassuranceCustomersContacts extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_BancassuranceCustomersContacts';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
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
        'DeletedBy'
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
        return $this->belongsTo(BancassuranceCustomers::class, 'CustomerID', 'Id');
    }
}
