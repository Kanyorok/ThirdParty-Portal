<?php
// app/Models/Assets/Accounting/DepreciationRunLine.php
namespace App\Models\Assets\Accounting;
use Illuminate\Database\Eloquent\Model;

class DepreciationRunLine extends Model {
  protected $table='t_DepreciationRunLines'; protected $primaryKey='Id'; public $timestamps=false;
  protected $fillable=['RunID','AssetID','BookID','MethodUsed','OpeningNBV','DepAmount','ClosingNBV','Months','Notes','CreatedOn'];
}
