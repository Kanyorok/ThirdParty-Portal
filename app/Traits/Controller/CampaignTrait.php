<?php

namespace App\Traits\Controller;

use App\Enums\CampaignStatusEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\CRM\Campaign;
use App\Models\CRM\MarketingList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

trait CampaignTrait
{
    public function getCampaigns(Builder $query, User $actor, array $with = [], MarketingList $list = null): JsonResponse
    {
        if (!empty($with)) {
            $query->with($with);
        }
        $status = collect([CampaignStatusEnum::Sent, CampaignStatusEnum::Sending, CampaignStatusEnum::Failed]);

        //check for approval.
        if ($actor->hasPermissionTo(PermissionEnum::CampaignApproval->value)) {
            $status->add(CampaignStatusEnum::Approval);
        }

        $query->where(function (Builder $builder) use ($actor, $status) {
            $builder->where(function (Builder $builder) use ($status) {
                $builder->whereIn('t_Campaigns.Status', $status->toArray());
            })->orWhere(function (Builder $builder) use ($actor) {
                $builder->orwhere('t_Campaigns.CreatedBy', $actor->Id)
                    ->where('t_Campaigns.Status', CampaignStatusEnum::Draft);
            });
        });
        try {
            return Datatables::of($query->lock('WITH(NOLOCK)')->select('*')->withCount('contacts'))->addIndexColumn()
                ->addColumn('action', function (Campaign $campaign) use ($list) {
                    $route = ($list instanceof MarketingList) ? route('loans-campaigns.show', [$list->MarketingListID, $campaign->CampaignID]) : route('campaigns.show', [$campaign->CampaignID]);
                    return '<a href="' . $route . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('Status', function (Campaign $campaign) {
                    return $campaign->Status->name;
                })->editColumn('contacts_count', function ($campaign) {
                    return number_format($campaign->contacts_count);
                })->editColumn('CreatedOn', function (Campaign $campaign) {
                    return $campaign->CreatedOn->format('d M, Y h:i A');
                })->editColumn('Notes', function (Campaign $campaign) {
                    return Str::limit($campaign->Notes);
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                                                                                                       'dbl_click_url' => function (Campaign $campaign) use ($list) {
                                                                                                        return ($list instanceof MarketingList) ? route('loans-campaigns.show', [$list->MarketingListID, $campaign->CampaignID]) : route('campaigns.show', [$campaign->CampaignID]);
                                                                                                       },
                                                                                                      ])->rawColumns(['action'])->make();
        } catch (\Exception $e) {
        }

        return $this->errored('fetching data failed, try again later');
    }
}
