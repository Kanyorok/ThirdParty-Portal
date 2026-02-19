<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $table = 't_HREmployeeDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'FileName',
        'FilePath',
        'Category',
        'Description',
        'UploadedBy',
        'UploadedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'UploadedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
