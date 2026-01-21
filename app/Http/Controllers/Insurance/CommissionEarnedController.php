<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Insurance\InsurancePolicyStatus;
use App\Http\Controllers\Controller;
use App\Models\Insurance\BancassurancePolicy;
use Illuminate\Http\Request;

class CommissionEarnedController extends Controller
{
    public function index(Request $request)
    {
        $claims = BancassurancePolicy::where('ReferralID', '!=', null)
            ->where('Status', InsurancePolicyStatus::Issued)
            ->where('IsActive', true)
            ->get();

        return view('bancassurance.commissions.earned.index', compact('claims'));
    }

}
