<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetGLSubType extends Model
{
    protected $table = 't_BudgetGLSubTypes';
    protected $primaryKey = 'Id';
    public static function getPrimaryKey(): string
    {
        return 'BudgetGLSubTypeId';
    }
}
