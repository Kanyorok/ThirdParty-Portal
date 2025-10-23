<?php
// app/Models/Assets/Accounting/AssetImpairment.php
namespace App\Models\Assets\Accounting;
use Illuminate\Database\Eloquent\Model;

class AssetImpairment extends Model {
  protected $table='t_AssetImpairments'; protected $primaryKey='Id'; public $timestamps=false;
  protected $fillable=['AssetID','BookID','TestDate','OldNBV','RecoverableAmount','Remarks','CreatedBy','CreatedOn'];
}
