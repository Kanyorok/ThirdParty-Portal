<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketSourceEnum;
use App\Models\CodeDetail;
use App\Models\Team;
use App\Models\User;
use App\Services\StaticListsService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NewTicketRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticket_title' => ['required', 'string', 'max:255'],
            'ticket_description' => ['required', 'string'],
            'ticket_category' => ['required'],
            'ticket_user' => ['required'],
            'ticket_watchers' => ['nullable', 'array', 'max:10'],
            'ticket_source' => ['required', Rule::enum(TicketSourceEnum::class)],
            'ticket_priority' => ['required', Rule::enum(TicketPriorityEnum::class)],
            'ticket_start' => ['nullable', 'required_with:ticket_end', 'date_format:"Y-m-d"'],
            'ticket_end' => ['nullable', 'required_with:ticket_start', 'date_format:"Y-m-d"'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getEnd(Carbon $start): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d', $this->validated('ticket_end'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages([
                'ticket_start' => 'invalid date format',
            ]);
        }

        if ($end->endOfDay()->lte($start)) {
            throw ValidationException::withMessages([
                'ticket_end' => 'should be after start.',
            ]);
        }

        /*  $diffInMinutes = $start->diffInMinutes($end, true);

          if ($diffInMinutes < 0) {
              throw ValidationException::withMessages([
                  'ticket_end' => 'duration should be less least 1 minute.',
              ]);
          }*/


        return $end->endOfDay()->subSecond();
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): ?Carbon
    {
        if (empty($this->validated('ticket_start'))) {
            return null;
        }

        $start = Carbon::createFromFormat('Y-m-d', $this->validated('ticket_start'));
        if ($start instanceof Carbon) {
            return $start->startOfDay();
        }

        throw ValidationException::withMessages([
            'ticket_start' => 'invalid date format',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getSource(): TicketSourceEnum
    {
        try {
            return TicketSourceEnum::fromValue($this->validated('ticket_source'));
        } catch (\Exception) {
        }
        throw ValidationException::withMessages([
            'ticket_source' => 'invalid source',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getPriority(): TicketPriorityEnum
    {
        try {
            return TicketPriorityEnum::fromValue($this->validated('ticket_priority'));
        } catch (\Exception) {
        }
        throw ValidationException::withMessages([
            'ticket_priority' => 'invalid ticket priority',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getAssignee(string $assignee = null): User|Team
    {
        $assignee = ($assignee) ?? $this->validated('ticket_user');
        if (Str::startsWith($assignee, 't#')) {
            $arr = explode('#', $assignee);
            array_shift($arr);
            $team = Team::query()->where('TeamID', implode('', $arr))->first();
            if (($team instanceof Team) && $team->users()->count() > 0) {
                return $team;
            }
            throw ValidationException::withMessages([
                'ticket_user' => 'invalid team or has no users',
            ]);
        }

        $user = User::query()->where('UserID', Str::upper($assignee))->first();
        if ($user instanceof User) {
            return $user;
        }
        throw ValidationException::withMessages([
            'ticket_user' => 'invalid user selected.',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getCategory(): CodeDetail
    {
        $category = StaticListsService::getRawList(StaticListsService::TicketCategories)->where('ID', $this->validated('ticket_category'))->first();
        if ($category instanceof CodeDetail) {
            return $category;
        }
        throw ValidationException::withMessages([
            'ticket_category' => 'Category Not Found',
        ]);
    }

    public function getWatchers(): Collection
    {
        $watchers = $this->validated('ticket_watchers');
        $Actors = collect([]);
        if (!is_array($watchers)) {
            return $Actors;
        }
        foreach ($watchers as $watcher) {
            try {
                $actor = $this->getAssignee($watcher);
            } catch (\Exception) {
                continue;
            }

            $Actors->add($actor);
        }
        return $Actors;
    }

}
