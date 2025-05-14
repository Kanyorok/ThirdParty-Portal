<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidSubmission extends Model
{
    protected $table = 't_BidSubmissions'; // Table name in the database
    //protected $primaryKey = 'TenderRef'; // Adjust if your primary key is different (e.g., 'BidID')

    protected $fillable = [
        'TenderRef',
        'SupplierName',
        'SubmissionMode',
        'ReceivedAt',
        'RecordedBy',
        'Remarks',
        'Documents',
    ];

    protected $casts = [
        'ReceivedAt' => 'datetime', // Cast ReceivedAt as a datetime object
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}