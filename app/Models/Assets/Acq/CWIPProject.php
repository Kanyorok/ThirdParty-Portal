<?php

// app/Models/Assets/Acq/CWIPProject.php

namespace App\Models\Assets\Acq;

use Illuminate\Database\Eloquent\Model;

class CWIPProject extends Model
{
    protected $table = 't_CWIPProjects';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['ProjectCode','ProjectName','StartDate','EndDate','Status','ClassID','LocationID','CapexBudget','Notes','IsActive','CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'];
}
