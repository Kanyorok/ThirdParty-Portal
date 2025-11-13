<?php

namespace App\Http\Controllers\API\CRDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CRDBGeneralLedgerController extends Controller
{
    public function syncGeneralLedgers()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'General Ledger Synced Successfully',
        ]);
    }
}
