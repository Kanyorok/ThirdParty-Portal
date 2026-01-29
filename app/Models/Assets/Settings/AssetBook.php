<?php

// app/Models/Assets/Settings/AssetBook.php

namespace App\Models\Assets\Settings;

use Illuminate\Database\Eloquent\Model;

class AssetBook extends Model
{
    protected $table = 't_AssetBooks';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Code','Name','IsActive','CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'];
}
