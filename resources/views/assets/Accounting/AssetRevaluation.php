<?php
// app/Models/Assets/Accounting/AssetRevaluation.php
namespace App\Models\Assets\Accounting;
use Illuminate\Database\Eloquent\Model;

class AssetRevaluation extends Model {
  protected $table='t_AssetRevaluations'; protected $primaryKey='Id'; public $timestamps=false;
  protected $fillable=['AssetID','BookID','RevalDate','OldNBV','NewFairValue','Remarks','CreatedBy','CreatedOn'];
}
