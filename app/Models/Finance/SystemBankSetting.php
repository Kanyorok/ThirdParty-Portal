<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemBankSetting extends Model
{
    use SoftDeletes;

    protected $table = 't_SystemBankSetting';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'BankName','ShortName','BankCode','SwiftCode','ClearingCode','Address1','Address2',
        'CityID','CountryID','ZipCode','Phone1','Phone2','Mobile','Fax','EmailID','Website',
        'BankRegNumber','AuditedDate','BankTypeID','ImageID','IsActive','CreatedBy','ModifiedBy',
        'SupervisedBy','SupervisedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'AuditedDate' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'SupervisedOn' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(\App\Models\Core\Country::class, 'CountryID', 'Id');
    }
}
