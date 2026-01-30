<?php

// app/Models/Assets/Master/AssetHistory.php

namespace App\Models\Assets\Master;

use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    protected $table = 't_AssetHistory';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['AssetID','EventType','EventDate','Reference','Remarks','CreatedBy','CreatedOn'];
}
