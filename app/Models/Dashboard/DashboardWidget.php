<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $table = 't_DashboardWidgets';
    public $timestamps = false; // using custom ERP timestamp columns
    protected $primaryKey = 'Id';
    protected $fillable = [
        'Key','Name','Module','Type','Description','View','DataEndpoint','DefaultFilters','DefaultW','DefaultH','IsActive','CreatedBy','CreatedOn','ModifiedBy','ModifiedOn','DeletedBy',
    ];
}
