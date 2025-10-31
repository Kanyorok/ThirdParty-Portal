<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Core\IntegrationsEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Settings\APICredential;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Handle the incoming request.
     * @throws AuthorizationException
     */
    public function lists(): View
    {
        $this->authorize('view', CodeDetail::class);
        return view('settings.lists');
    }

    /**
     * Handle the incoming request.
     * @throws AuthorizationException
     */
    public function users(): View
    {
        $this->authorize('settings', User::class);
        return view('settings.users');
    }

    /**
     * Handle the incoming request.
     * @throws AuthorizationException
     */
    public function integrations(): View
    {
        $this->authorize('viewAny', APICredential::class);
        $all = APICredential::all();
        $cbs = $all->where('Integration', IntegrationsEnum::CoreBanking->value)->first();
        $email = $all->where('Integration', IntegrationsEnum::Email->value)->first();
        $channel = $all->where('Integration', IntegrationsEnum::Channels->value)->first();
        $sms = $all->where('Integration', IntegrationsEnum::SMS->value)->first();
        $fb = $all->where('Integration', IntegrationsEnum::Facebook->value)->first();
        $x = $all->where('Integration', IntegrationsEnum::Twitter->value)->first();
        $ai = $all->where('Integration', IntegrationsEnum::LLM->value)->first();
        $InfoBip = $all->where('Integration', IntegrationsEnum::InfoBip->value)->first();
        $srsConfig = $all->where('Integration', IntegrationsEnum::ReportService->value)->first();
    $org = $all->where('Integration', IntegrationsEnum::Organization->value)->first();

        return view('settings.integrations')
            ->with('twitterConfig', ($x instanceof APICredential) ? $x->Configuration : new APICredential)
            ->with('facebookConfig', ($fb instanceof APICredential) ? $fb->Configuration : new APICredential)
            ->with('smsConfig', ($sms instanceof APICredential) ? $sms->Configuration : new APICredential)
            ->with('cbsConfig', ($cbs instanceof APICredential) ? $cbs->Configuration : new APICredential)
            ->with('emailConfig', ($email instanceof APICredential) ? $email->Configuration : new APICredential)
            ->with('channelsConfig', ($channel instanceof APICredential) ? $channel->Configuration : new APICredential)
            ->with('llmConfig', ($ai instanceof APICredential) ? $ai->Configuration : new APICredential)
            ->with('infoBipConfig', ($InfoBip instanceof APICredential) ? $InfoBip->Configuration : new APICredential)
            ->with('srsConfig', ($srsConfig instanceof APICredential) ? $srsConfig->Configuration : new APICredential)
            ->with('orgConfig', ($org instanceof APICredential) ? $org->Configuration : new APICredential);
    }
}
