<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class ContractMilestone extends Model
{
    protected $table = 't_ContractMilestones';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ContractSourceType',
        'ContractSourceID',
        'MilestoneNo',
        'Title',
        'Description',
        'PlannedDueDate',
        'ValueType',
        'ValuePercent',
        'ValueAmount',
        'AcceptanceRequired',
        'Status',
        'AcceptedBy',
        'AcceptedOn',
        'WaivedBy',
        'WaivedOn',
        'WaiveReason',
    ];

    protected $casts = [
        'PlannedDueDate' => 'date',
        'ValuePercent' => 'float',
        'ValueAmount' => 'float',
        'AcceptanceRequired' => 'boolean',
        'AcceptedOn' => 'datetime',
        'WaivedOn' => 'datetime',
    ];

    public function checklistItems()
    {
        return $this->hasMany(ContractMilestoneChecklist::class, 'MilestoneID', 'Id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'AcceptedBy', 'Id');
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'WaivedBy', 'Id');
    }
}

