<?php

namespace App\Http\Controllers\Marketing\Campaign;

use App\Enums\CampaignStatusEnum;
use App\Enums\CampaignTypeEnum;
use App\Enums\Core\VisibilityEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\CampaignRequest;
use App\Models\BR\DebtProduct;
use App\Models\Campaign;
use App\Models\MarketingList;
use App\Services\Marketing\CampaignService;
use App\Traits\Controller\CampaignTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CampaignController extends Controller
{
    use CampaignTrait;
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
        $this->authorizeResource(Campaign::class);
    }

    /**
     * Display a listing of the campaigns.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $actor = $request->user();
            $query = Campaign::query()
                ->whereNotIn('t_Campaigns.MarketingListId', MarketingList::query()
                    ->where('t_MarketingLists.Source', DebtProduct::getPrimaryKey())->select('t_MarketingLists.MarketingListID'));

            return $this->getCampaigns($query, $actor);
        }

        return view('marketing.campaigns.index')
            ->with('MarketingLists', MarketingList::query()->where(function ($q) use ($request) {
                $q->where('Visibility', VisibilityEnum::Public->value)
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('Visibility', VisibilityEnum::Private->value)
                            ->where('CreatedBy', $request->user()->Id);
                    });
            })->select(['slug', 'Label'])->get());
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(CampaignRequest $request): JsonResponse
    {
        $list = $request->getList();
        $actor = $request->user();
        $type = $request->getType();
        try {
            $campaign = DB::transaction(static function () use ($type, $actor, $request, $list) {
                return CampaignService::create($list, $request->string('Label', 'Non Labeled Campaign')->toString(), $actor, $type, $request->string('Notes', '')->toString())->campaign;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Throwable|Exception $e) {
            Log::error('Error create campaign ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('campaign created.', route: route('campaigns.show', $campaign->CampaignID));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Campaign $campaign): RedirectResponse|View
    {
        if ($campaign->list->Source === DebtProduct::getPrimaryKey()) {
            return redirect()->back()->with(['fail' => 'campaign not found, or invalid']);
        }

        return view('marketing.campaigns.show', compact('campaign'))
            ->with('canApprove', $request->user()->can('approve', $campaign))
            ->with('hasProgress', ($campaign->Processing || $campaign->Status->value === CampaignStatusEnum::Sending->value));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->Status->value !== CampaignStatusEnum::Draft->value) {
            return $this->errored('Campaign has already run, cannot be edited');
        }

        $request->validate([
            'Subject' => [Rule::requiredIf($campaign->Type->value === CampaignTypeEnum::Email->value), 'max:200'],
            'Content' => ['required', 'string', 'min:5', 'max:50000']
        ]);

        $campaign->update([
            'Label' => ($campaign->Type->value === CampaignTypeEnum::Email->value) ? $request->Subject : $campaign->Label,
            'Details' => Str::of($request->Content)->remove(["\r", "\n", "\t", "\0", "\x0B"])->replace("\u{A0}", " ")->toString(),
            'ModifiedBy' => $request->user()->Id,
        ]);

        return $this->succeeded('saved successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->Processing) {
            return $this->errored('campaign contacts processing is not done, please wait.');
        }

        $campaign->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $request->user()->Id,
        ])->save();

        return $this->succeeded('Campaign canceled successfully', route('campaigns.index'));
    }
}
