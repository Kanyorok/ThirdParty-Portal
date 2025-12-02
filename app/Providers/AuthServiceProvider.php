<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\ThirdParty\ThirdPartiesBankDetails;
use App\Policies\ThirdPartyBankDetailPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ThirdPartiesBankDetails::class => ThirdPartyBankDetailPolicy::class,
        \App\Models\Core\Approval\WorkflowStage::class => \App\Policies\WorkflowStagePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
