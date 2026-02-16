<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementMode extends Model
{
    use HasFactory;

    protected $table = 't_ProcurementModes';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
