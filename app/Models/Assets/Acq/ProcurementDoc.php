<?php
// app/Models/Assets/Acq/ProcurementDoc.php
namespace App\Models\Assets\Acq;
use Illuminate\Database\Eloquent\Model;

class ProcurementDoc extends Model {
    protected $table = 't_ProcurementDocs';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['DocType','DocNo','SupplierID','SupplierName','DocDate','Currency','Amount','Status','ExternalId','CreatedOn'];
}
