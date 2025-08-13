<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CommissionPayoutController extends Controller
{
    public function index()
    {
        $payouts = DB::table('t_BancassuranceCommissionPayouts as p')
            ->leftJoin('t_BancassuranceCommissionsEarned as e', 'p.CommissionEarnedID', '=', 'e.Id')
            ->leftJoin('t_BancassurancePolicies as pol', 'e.PolicyID', '=', 'pol.Id')
            ->select(
                'p.*',
                'e.EarnedAmount',
                'e.EarnedByType',
                'e.EarnedByID',
                'pol.PolicyNumber'
            )
            ->orderByDesc('p.PaymentDate')
            ->get();

        return view('bancassurance.commissions.payouts.index', compact('payouts'));
    }
}
