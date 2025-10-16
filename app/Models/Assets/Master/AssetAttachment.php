<?php
// app/Models/Assets/Master/AssetAttachment.php
namespace App\Models\Assets\Master;
use Illuminate\Database\Eloquent\Model;

class AssetAttachment extends Model {
    protected $table = 't_AssetAttachments';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = ['AssetID','DocType','FileName','DMSPath','UploadedBy','UploadedOn'];
}
