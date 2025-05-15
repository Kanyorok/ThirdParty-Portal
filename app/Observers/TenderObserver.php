<?php

namespace App\Observers;

use App\Models\Procurement\Tender;
use Illuminate\Support\Facades\DB;

class TenderObserver {
    /**
     * Handle the Tender "create" event.
     *
     * @param  \App\Models\Procurement\Tender  $tender
     * @return void
     */
    public function creating(Tender $tender)
    {
        $year = now()->year;
        $lastTender = DB::table('t_Tenders')
            ->where('TenderNo', 'like', "TENDER-$year%")
            ->orderBy('TenderNo', 'desc')
            ->first();
        $nextNumber = $lastTender ? (int) substr($lastTender->TenderNo, -4) + 1 : 1;
        $tender->TenderNo = sprintf('TENDER-%d-%04d', $year, $nextNumber);
    }
}