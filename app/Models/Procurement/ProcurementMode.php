<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementMode extends Model
{
    use HasFactory;

    protected $table = 't_ProcurementModes';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'Name',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'UniqueCode',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function timelines()
    {
        return $this->hasMany(ModeTimeline::class, 'ProcurementModeId');
    }

    public function tenders()
    {
        return $this->hasMany(Tender::class, 'ProcurementModeId');
    }
}
