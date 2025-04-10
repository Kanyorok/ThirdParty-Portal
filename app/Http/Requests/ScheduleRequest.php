<?php

namespace App\Http\Requests;

use App\Models\BR\Account;
use App\Models\BR\Client;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client' => ['required_if:_type,call', 'array', 'min:1', 'max:500'],
            'client.*' => ['required', 'max:9000'],
            'schedule_title' => ['required_if:_type,meeting', 'max:255'],
            'branches' => ['nullable', 'array', 'max:255'],
            #'_type' => ['required', 'string', Rule::in(['call','meeting'])],
            'schedule_start' => 'required|date_format:"Y-m-d H:i"|before:end',
            'schedule_end' => 'required|date_format:"Y-m-d H:i"|after:start',
            'notes' => 'required|min:1',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getEnd(Carbon $start):Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d H:i', $this->input('schedule_end'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages([
                'schedule_start' => 'invalid date format',
            ]);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'schedule_end' => 'should be after start.',
            ]);
        }

        $diffInMinutes = $start->diffInMinutes($end,true);

        if ($diffInMinutes < 0) {
            throw ValidationException::withMessages([
                'schedule_end' => 'duration should be less least 1 minute.',
            ]);
        }

        if ($diffInMinutes > 480) {//8 hours
            throw ValidationException::withMessages([
                'schedule_end' => 'duration can only be a maximum of 8 hours.',
            ]);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getStart():Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $this->input('schedule_start'));
        if ($start instanceof Carbon) {
            return $start;
        }
        throw ValidationException::withMessages([
            'schedule_start' => 'invalid date format',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getClients():Collection
    {
        $clients = collect([]);
        $ids = $this->input('client');
        if (!empty($ids)) {
            $clients = Client::query()->whereIn('ClientID', $ids)->lock('WITH(NOLOCK)')->select(['ClientID', 'Name'])->get();
        }
        $branches = $this->input('branches');
        if (!empty($branches)) {
            $clients2 = Account::query()->whereIn('OurBranchID', $branches)->select(['ClientID', 'Name'])->get();
            if ($clients->isEmpty()) {
                $clients = $clients2;
            } else {
                $clients = collect(array_merge($clients->pluck('ClientID')->toArray(), $clients2->pluck('ClientID')->toArray()))->unique();
            }

        }

        if ($clients->isEmpty()) {
            throw ValidationException::withMessages([
                'client' => 'No clients found.',
            ]);
        }

        return $clients;

        //$clients = Client::query()->whereIn('ClientID',$ids)->lock('WITH(NOLOCK)')->select(['ClientID','Name'])->get();

    }
}
