<?php
// app/Models/Assets/Acq/CWIPLine.php
namespace App\Models\Assets\Acq;
use Illuminate\Database\Eloquent\Model;

class CWIPLine extends Model {
    protected $table = 't_CWIPLines';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['ProjectID','RefType','RefID','Description','Quantity','UnitCost','TaxAmount','ReceivedDate','ClassID','LocationID','BookID','Status','IsCapitalizable','Notes','CreatedOn','ModifiedOn'];
}
