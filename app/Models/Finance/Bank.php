<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 't_Banks';
    protected $primaryKey = 'BankID';

    protected $fillable = [
        'BankName', 'ShortName', 'BankCode', 'SwiftCode', 'ClearingCode',
        'CountryID', 'EmailID', 'Phone', 'Website', 'IsActive'
    ];

    // A bank has many branches
    public function branches()
    {
        return $this->hasMany(BankBranch::class, 'BankID', 'BankID');
    }
}
