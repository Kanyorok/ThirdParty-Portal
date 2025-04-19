<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 't_Suppliers';

    protected $fillable = [
        'SupplierName',
        'ContactEmail',
        'ContactPhone',
        'Address',
        'IsPrequalified',
    ];

    protected $primaryKey = 'Id';

    public function getIsPrequalifiedAttribute($value)
    {
        return (bool) $value;
    }

    public function setIsPrequalifiedAttribute($value)
    {
        $this->attributes['IsPrequalified'] = (bool) $value;
    }
}
