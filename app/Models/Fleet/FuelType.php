<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FuelType extends Model
{
    protected $table = 't_FuelTypes';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $fillable = ['Name', 'IsActive'];
}
