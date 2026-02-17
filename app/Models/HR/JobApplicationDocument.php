<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobApplicationDocument extends Model
{
    protected $table = 't_HRJobApplicationDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ApplicationID',
        'FileName',
        'FilePath',
        'Category',
        'Description',
        'UploadedBy',
        'UploadedOn',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }
}
