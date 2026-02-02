<?php

namespace App\Services\Procurement\Tendering;

use App\Models\Procurement\Tender;

class TenderService
{
    public static function ID(): string
    {
        $number = Tender::query()->withTrashed()->count();
        $year = now()->year;
        do {
            $number++;
            $slug = sprintf('TENDER-%d-%04d', $year, $number);
        } while (Tender::where('TenderNo', $slug)->withTrashed()->exists());

        return $slug;
    }
}
