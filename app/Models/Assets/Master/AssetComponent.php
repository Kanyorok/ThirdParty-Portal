<?php

// app/Models/Assets/Master/AssetComponent.php

namespace App\Models\Assets\Master;

use Illuminate\Database\Eloquent\Model;

class AssetComponent extends Model
{
    protected $table = 't_AssetComponents';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'AssetID','ComponentName','SerialNumber','Quantity','AcquisitionDate','Cost',
        'IsCritical','IsActive','CreatedOn','ModifiedOn',
    ];
}
