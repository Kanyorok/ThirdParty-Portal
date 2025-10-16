<?php
// app/Models/Assets/Settings/AssetNumberingRule.php
namespace App\Models\Assets\Settings;

use Illuminate\Database\Eloquent\Model;

class AssetNumberingRule extends Model
{
    protected $table = 't_AssetNumberingRules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name','Scope','ClassID','LocationID','BookID',
        'Prefix','Suffix','PadLength','NextSeq','ResetPeriod',
        'LastResetOn','CodePattern','IsActive','CreatedOn'
    ];
}
