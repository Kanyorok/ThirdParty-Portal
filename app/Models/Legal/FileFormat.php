<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class FileFormat extends Model
{
    protected $table = 't_FileFormats';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'MimeType',
        'IsActive',
    ];
}
