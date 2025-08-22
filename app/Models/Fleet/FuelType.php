<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FuelType extends Model
{
    protected $table = 't_FuelTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['FuelName', 'IsActive', 'FuelTypeCode', 'Description', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn'];
}
