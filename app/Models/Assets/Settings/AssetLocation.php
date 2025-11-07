<?php
// app/Models/Assets/Settings/AssetLocation.php
namespace App\Models\Assets\Settings;
use Illuminate\Database\Eloquent\Model;

class AssetLocation extends Model {
    protected $table = 't_AssetLocations';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Code','Site','Building','Floor','Room','ParentID','IsActive','CreatedOn','ModifiedOn'];
}
