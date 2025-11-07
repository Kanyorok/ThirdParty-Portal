<?php
// app/Models/Assets/Accounting/AssetImprovement.php
namespace App\Models\Assets\Accounting;
use Illuminate\Database\Eloquent\Model;

class AssetImprovement extends Model {
  protected $table='t_AssetImprovements'; protected $primaryKey='Id'; public $timestamps=false;
  protected $fillable=['AssetID','BookID','DocNo','DocDate','Description','Amount','Treatment','Posted','CreatedOn'];
}
