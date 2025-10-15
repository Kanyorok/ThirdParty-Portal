<?php
// app/Models/Assets/Acq/CapitalizationLine.php
namespace App\Models\Assets\Acq;
use Illuminate\Database\Eloquent\Model;

class CapitalizationLine extends Model {
    protected $table = 't_CapitalizationLines';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['BatchID','SourceType','CWIPLineID','AssetID','AssetCode','AssetName','ClassID','LocationID','BookID','DepStartDate','CapitalizeAmt','ResidualPct','Notes','CreatedOn'];
}
