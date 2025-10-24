<?php
// app/Models/Assets/Master/AssetMeterReading.php
namespace App\Models\Assets\Master;
use Illuminate\Database\Eloquent\Model;

class AssetMeterReading extends Model {
    protected $table = 't_AssetMeterReadings';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['MeterID','Reading','ReadingDate','EnteredBy','EnteredOn'];
}
