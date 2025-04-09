<?php

namespace App\Http\Controllers\Auth;


use App\Enums\EmailStatusEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Schedule;
use App\Models\ScheduleUser;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

class DashboardController extends Controller
{
    protected const MONTHS = 6;
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return View
     */
    public function __invoke(Request $request): View
    {
        $actor = $request->user();
        $data = [
            'leads' => [
                'line' => ['labels' => [], 'converted' => []],
                'donut' => ['labels' => []],
                'total' => 0,
            ],
            'campaigns' => ['active' => 0, 'sent' => 0],
            'schedule' => ['calls' => 0, 'appointments' => 0, 'total' => 0],
            'tickets' => ['active' => 0]
        ];
        $total_leads = Lead::query()->where('t_Leads.RelationshipManagerID', $actor->Id)->count();
        $active_campaigns = Campaign::query()->where('t_Campaigns.Status', EmailStatusEnum::Draft->value)->count();
        /* $total_schedule = Schedule::query()->whereIn('t_Schedule.ScheduleID', ScheduleUser::query()->where('UserID', $request->user()->Id)->select('t_ScheduleUsers.ScheduleId'))
             ->where('t_Schedule.StartOn', '>=', Carbon::now()->startOfDay())->count();*/

        $schedule_calls = Schedule::query()->where('t_Schedule.Type', Call::getPrimaryKey())->where('t_Schedule.StartOn', '>=', Carbon::now())
            ->whereIn('t_Schedule.ScheduleID', ScheduleUser::query()->where('UserID', $request->user()->Id)->select('t_ScheduleUsers.ScheduleId'))
            ->count();
        $schedule_meetings = Schedule::query()->where('t_Schedule.Type', Meeting::getPrimaryKey())->where('t_Schedule.StartOn', '>=', Carbon::now())
            ->whereIn('t_Schedule.ScheduleID', ScheduleUser::query()->where('UserID', $request->user()->Id)->select('t_ScheduleUsers.ScheduleId'))
            ->count();
        $tickets_active = Ticket::query()->where('t_Tickets.Status', TicketStatusEnum::Active->value)->where(function (Builder $query) use ($actor) {
            $query->where(function (Builder $query) use ($actor) {
                $query->where('t_Tickets.Owner', User::getPrimaryKey())->where('t_Tickets.OwnerID', $actor->Id);
            })->orWhere(function (Builder $query) use ($actor) {
                $query->where('t_Tickets.Owner', Team::getPrimaryKey())
                    ->whereIn('t_Tickets.OwnerID', $actor->teamUser()->select('t_TeamUser.TeamId'));
                // dd($request->user()->teams()->select('t_TeamUser.TeamId')->get('TeamId'));
            })->orWhere('t_Tickets.CreatedBy', $actor->Id
            )->orWhere(function (Builder $query) use ($actor) {
                $query->where('t_Tickets.Party', User::getPrimaryKey())->where('t_Tickets.PartyID', $actor->Id);
            });
        })->count();

        data_set($data, 'tickets.active', Number::abbreviate($tickets_active, ($tickets_active > 999) ? 1 : 0));

        data_set($data, 'campaigns.active', Number::abbreviate($active_campaigns, ($active_campaigns > 999) ? 1 : 0));
        //  data_set($data, 'schedule.total', Number::abbreviate($total_schedule, ($total_schedule > 999) ? 1 : 0));
        data_set($data, 'schedule.calls', Number::abbreviate($schedule_calls, ($schedule_calls > 999) ? 1 : 0));
        data_set($data, 'schedule.appointments', Number::abbreviate($schedule_meetings, ($schedule_meetings > 999) ? 1 : 0));

        data_set($data, 'leads.total', Number::abbreviate($total_leads, ($total_leads > 999) ? 1 : 0));
        data_set($data, 'leads.donut.labels', [LeadStatusEnum::Warm->name, LeadStatusEnum::Hot->name]);

        data_set($data, 'leads.line.converted', $this->converted($actor));
        data_set($data, 'leads.line.labels', $this->months());


        return view('auth.dashboard', compact('data'));
    }

    protected function months(): array
    {
        $dates = collect();
        $dateTime = Carbon::now()->startOfMonth()->subMonths(self::MONTHS);
        for ($i = 1; $i <= self::MONTHS; $i++) {
            $dateTime->addMonth();
            $dates->add($dateTime->format('M y'));
        }
        return $dates->toArray();
    }

    private function converted(User $actor): array
    {
        $data = collect();
        $dateTime = Carbon::now()->startOfMonth()->subMonths(self::MONTHS);
        for ($i = 1; $i <= self::MONTHS; $i++) {
            $dateTime->addMonth();
            $converted = 0;
            try {

                $converted = Lead::withTrashed()->whereBetween('DeletedOn', [$dateTime->copy()->startOfMonth(), $dateTime->copy()->endOfMonth()])
                    ->where('Status', LeadStatusEnum::Won->value)->where('RelationshipManagerID', $actor->Id)->count();
            } catch (\Exception) {
            }
            $data->add($converted);
        }

        return $data->toArray();
    }
}
