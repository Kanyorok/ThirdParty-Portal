<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Branch;
use Spatie\Permission\Models\Role;

class ModelRole extends Model
{
    protected $table = 't_ModelRoles';
    protected $fillable = ['model_id', 'model_type', 'role_id', 'BranchId'];
    public $timestamps = false;
    
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchId');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
