<?php

namespace App\Models\CRM\Training;

use App\Models\BR\Client;
use Illuminate\Database\Eloquent\Model;

class TrainingSessionFeedback extends Model
{
    protected $table = 't_CRMTrainingSessionFeedback';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'ClientID',
        'RatingContent',
        'RatingTrainer',
        'RatingRelevance',
        'Comments',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'SessionID');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }
}
