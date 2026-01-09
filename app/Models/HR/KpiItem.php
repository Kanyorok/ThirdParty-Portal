<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class KpiItem extends Model
{
    protected $table = 't_HRKPIItems';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Category',
        'CategoryID',
        'Unit',
        'UnitID',
        'Perspective',
        'PerspectiveID',
        'DefaultWeight',
        'Description',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'DefaultWeight' => 'decimal:2',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(KpiCategory::class, 'CategoryID');
    }

    public function unit()
    {
        return $this->belongsTo(KpiUnit::class, 'UnitID');
    }

    public function perspective()
    {
        return $this->belongsTo(KpiPerspective::class, 'PerspectiveID');
    }
}
