<?php
// app/Models/Assets/Master/AssetBookValue.php
namespace App\Models\Assets\Master;
use Illuminate\Database\Eloquent\Model;

class AssetBookValue extends Model {
    protected $table = 't_AssetBookValues';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'AssetID','BookID','AcquisitionCost','DepMethod','UsefulLifeMonths','ResidualPct',
        'DepStartDate','AccumDep','NBV','LastDepRunDate','IsActive','CreatedOn','ModifiedOn'
    ];
}
