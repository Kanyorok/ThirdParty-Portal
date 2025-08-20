<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $table = 't_VehicleTypes';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $fillable = ['Name', 'IsActive'];
}
