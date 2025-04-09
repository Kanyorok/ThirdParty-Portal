<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\BR\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSummaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Account $account): View
    {
        activity()->causedBy($request->user())->performedOn($account)->event('view')->log('Account ' . $account->AccountID . ' summary');
        return view('accounts.summary', [
            'account' => $account,
            'client' => $account->client,
            'transactions' => $account->transactions()->lock('WITH(NOLOCK)')->with(['type'])->latest('ValueDate')->limit(5)->get()
        ]);
    }
}
