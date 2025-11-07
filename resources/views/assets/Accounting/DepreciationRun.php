<?php
// app/Models/Assets/Accounting/DepreciationRun.php
namespace App\Models\Assets\Accounting;
use Illuminate\Database\Eloquent\Model;

class DepreciationRun extends Model {
  protected $table='t_DepreciationRuns'; protected $primaryKey='Id'; public $timestamps=false;
  protected $fillable=['BookID','PeriodStart','PeriodEnd','Status','Remarks','CreatedBy','CreatedOn','PostedBy','PostedOn'];
}
