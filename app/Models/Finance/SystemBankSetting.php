<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class SystemBankSetting extends Model
{
    protected $table = 't_SystemBankSetting';
    protected $primaryKey = 'Id';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'BankName','ShortName','BankCode','SwiftCode','ClearingCode','Address1','Address2',
        'CityID','CountryID','ZipCode','Phone1','Phone2','Mobile','Fax','EmailID','Website',
        'BankRegNumber','EmployerTaxPIN','AuditedDate','BankTypeID','ImageID','IsActive','CreatedBy','ModifiedBy',
        'SupervisedBy','SupervisedOn'
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
