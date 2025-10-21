<?php
// app/Models/Assets/Settings/InsuranceType.php
namespace App\Models\Assets\Settings;
use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model {
    protected $table = 't_InsuranceTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Code','Name','IsActive','CreatedOn','ModifiedOn'];
}
