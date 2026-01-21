<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class TrainingCategory extends Model
{
    protected $table = 't_HRTrainingCategories';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
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
        'IsActive'   => 'boolean',
        'CreatedOn'  => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn'  => 'datetime',
    ];
}
