<?php
// app/Models/Assets/Settings/AssetServiceProvider.php
namespace App\Models\Assets\Settings;
use Illuminate\Database\Eloquent\Model;

class AssetServiceProvider extends Model {
    protected $table = 't_AssetServiceProviders';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Name','Category','SupplierID','ContactEmail','ContactPhone','IsActive','CreatedOn','ModifiedOn'];
}
