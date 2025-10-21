<?php
// app/Models/Assets/Settings/MaintenanceType.php
namespace App\Models\Assets\Settings;
use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model {
    protected $table = 't_MaintenanceTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Code','Name','IsActive','CreatedOn','ModifiedOn'];
}
