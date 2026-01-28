<?php

// app/Models/Assets/Settings/FixedAssetClassBook.php

namespace App\Models\Assets\Settings;

use Illuminate\Database\Eloquent\Model;

class FixedAssetClassBook extends Model
{
    protected $table = 't_FixedAssetClassBooks';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'ClassID','BookID','DepMethod','UsefulLifeMonths','ResidualPct',
        'IsActive','CreatedBy','CreatedOn','ModifiedBy','ModifiedOn',
    ];
}
