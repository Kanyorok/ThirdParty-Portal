<?php
// app/Models/Assets/Accounting/LeaseAsset.php
namespace App\Models\Assets\Accounting;
use Illuminate\Database\Eloquent\Model;

class LeaseAsset extends Model {
  protected $table='t_LeaseAssets'; protected $primaryKey='Id'; public $timestamps=false;
  protected $fillable=['AssetID','BookID','LeaseCode','Commencement','TermMonths','DiscountRatePA','PaymentAmount','PaymentFreq','Status','CreatedOn'];
}
