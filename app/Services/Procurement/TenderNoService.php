<?php

namespace App\Providers;

use App\Models\Procurement\Tender;
use App\Observers\TenderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Tender::observe(TenderObserver::class);
    }
}