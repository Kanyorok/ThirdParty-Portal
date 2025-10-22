<?php
// app/Models/Assets/Settings/DisposalMethod.php
namespace App\Models\Assets\Settings;
use Illuminate\Database\Eloquent\Model;

class DisposalMethod extends Model {
    protected $table = 't_DisposalMethods';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['Code','Name','IsActive','CreatedOn','ModifiedOn'];
}
