<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model
{
    use HasFactory;

    protected $table = 't_BankBranches';
    protected $primaryKey = 'BranchID';

    protected $fillable = [
        'BankID', 'BranchCode', 'BranchName', 'Address1', 'Address2', 
        'CityID', 'CountryID', 'ZipCode', 'Phone', 'EmailID', 'IsActive'
    ];

    // A branch belongs to a bank
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'BankID', 'BankID');
    }
}
