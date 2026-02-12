<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class ContractMilestoneChecklist extends Model
{
    protected $table = 't_ContractMilestoneChecklist';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'MilestoneID',
        'ItemDescription',
        'Required',
        'IsFulfilled',
        'FulfilledBy',
        'FulfilledOn',
        'Notes',
        'EvidenceDocId',
    ];

    protected $casts = [
        'Required' => 'boolean',
        'IsFulfilled' => 'boolean',
        'FulfilledOn' => 'datetime',
    ];

    public function milestone()
    {
        return $this->belongsTo(ContractMilestone::class, 'MilestoneID', 'Id');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'FulfilledBy', 'Id');
    }
}

