<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class UserDashboardWidget extends Model
{
    protected $table = 'user_dashboard_widgets';
    protected $fillable = [
        'user_id','widget_key','x','y','w','h','sort_order','config',
    ];

    protected $casts = [
        'config' => 'array',
    ];
}
