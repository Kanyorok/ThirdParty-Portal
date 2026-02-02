<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GLDynamicController extends Controller
{
    public function getTypeGroups($accountTypeId)
    {
        return DB::table('t_GLTypeGroups')
            ->where('GLAccountTypeID', $accountTypeId)
            ->select('Id', 'Description')
            ->get();
    }

    public function getSubTypes($groupId)
    {
        return DB::table('t_GLSubAccountTypes')
            ->where('GLTypeGroupID', $groupId)
            ->select('Id', 'Description')
            ->get();
    }
}
