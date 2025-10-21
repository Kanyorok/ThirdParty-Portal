<?php
// app/Models/Assets/Settings/FixedAssetClass.php
namespace App\Models\Assets\Settings;
use Illuminate\Database\Eloquent\Model;

class FixedAssetClass extends Model {
    protected $table = 't_FixedAssetClasses';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'Code','Name','DepMethod','UsefulLifeMonths','ResidualPct','CapThreshold',
        'PoolingFlag','RevaluationAllowed','DefaultGLMap','IsActive',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'
    ];
}
