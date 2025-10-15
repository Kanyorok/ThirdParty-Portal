<?php
// app/Models/Assets/Master/AssetCalibrationEvent.php
namespace App\Models\Assets\Master;
use Illuminate\Database\Eloquent\Model;

class AssetCalibrationEvent extends Model {
    protected $table = 't_AssetCalibrationEvents';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'AssetID','MeterID','ProviderID','CertificateNo','CalibrationDate','NextDueDate','Result','Remarks','CreatedOn'
    ];
}
