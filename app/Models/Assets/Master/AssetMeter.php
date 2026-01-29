<?php

// app/Models/Assets/Master/AssetMeter.php

namespace App\Models\Assets\Master;

use Illuminate\Database\Eloquent\Model;

class AssetMeter extends Model
{
    protected $table = 't_AssetMeters';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'AssetID','MeterName','Unit','ReadingType','InitialReading','CurrentReading',
        'LastReadingDate','IsActive','CreatedOn','ModifiedOn',
    ];
}
