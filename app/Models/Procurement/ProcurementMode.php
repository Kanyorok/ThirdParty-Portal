<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementMode extends Model
{
    use HasFactory;

    protected $table = 't_ProcurementModes';

    protected $fillable = [
        'Name',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'UniqueCode',
    ];
}
