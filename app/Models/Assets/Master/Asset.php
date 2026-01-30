<?php

// app/Models/Assets/Master/Asset.php

namespace App\Models\Assets\Master;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 't_Assets';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'AssetCode','AssetName','ClassID','LocationID','CustodianID','Status',
        'AcquisitionDate','CapitalizationDate','Manufacturer','Model','SerialNumber','TagNo',
        'Notes','IsActive','CreatedBy','CreatedOn','ModifiedBy','ModifiedOn',
    ];
}
