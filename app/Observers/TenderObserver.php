<?php

namespace App\Observers;

use App\Models\Procurement\Tender;
use App\Services\Procurement\Tendering\TenderService;

class TenderObserver
{
    /**
     * Handle the Tender "create" event.
     *
     * @param Tender $tender
     * @return void
     */
    public function creating(Tender $tender): void
    {
        $tender->TenderNo = TenderService::ID();
    }
}
