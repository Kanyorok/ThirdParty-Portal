<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BRUser extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'OperatorID';
    //protected $connection = 'brcbs';
    //protected $table = 't_User';
    protected $connection = 'sqlsrv';
    protected $table = 'syn_csb_t_User';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
                         'Password',
                         'TrxPassword',
                        ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }

    protected function casts(): array
    {
        return [
                'Password' => 'hashed',
                'TrxPassword' => 'hashed',
               ];
    }
}
