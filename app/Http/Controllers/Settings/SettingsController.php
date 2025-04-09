<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Core\IntegrationsEnum;
use App\Http\Controllers\Controller;
use App\Models\APICredential;
use App\Models\CodeDetail;
use App\Models\User;
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

        return view('settings.integrations')
            ->with('twitterConfig', ($x instanceof APICredential) ? $x->Configuration : null)
            ->with('facebookConfig', ($fb instanceof APICredential) ? $fb->Configuration : null)
            ->with('smsConfig', ($sms instanceof APICredential) ? $sms->Configuration : null)
            ->with('cbsConfig', ($cbs instanceof APICredential) ? $cbs->Configuration : null)
            ->with('emailConfig', ($email instanceof APICredential) ? $email->Configuration : null)
            ->with('channelsConfig', ($channel instanceof APICredential) ? $channel->Configuration : null)
            ->with('llmConfig', ($ai instanceof APICredential) ? $ai->Configuration : null)
            ->with('infoBipConfig', ($InfoBip instanceof APICredential) ? $InfoBip->Configuration : null);
    }
}
