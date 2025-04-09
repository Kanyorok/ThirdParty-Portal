<?php

namespace App\Http\Requests\Board;

use App\Models\Board;
use App\Models\BR\Client;
use App\Models\Committee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NewBoardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ClientID' => ['required', 'string', 'max:200'],
            'BoardCommittees' => ['required', 'array', 'min:1', 'max:20'],
            'BoardCommittees.*' => ['required', Rule::exists('t_Committees', 'CommitteeID')],
            'BoardMemberRole' => ['nullable', 'string', 'max:200'],
            'BoardMemberNotes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function getCommittees():array
    {
        return Committee::query()->whereIn('CommitteeID', $this->validated('BoardCommittees'))->select('Id')->pluck('Id')->toArray();
    }

    public function getBaseClient(): Client
    {
        $client = Client::query()->where('ClientID', $this->validated('ClientID'))->first();
        if ($client instanceof Client) {
            if (Board::query()->where('ClientID', $client->ClientID)->exists()) {
                throw ValidationException::withMessages([
                    'ClientID' => 'client already has a board member'
                ]);
            }
            return $client;
        }
        throw ValidationException::withMessages([
            'ClientID' => 'invalid Member Number'
        ]);
    }

    public function generateID(): string
    {
        $number = Board::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::upper(Str::slug('BM-' . Str::padLeft(($number), 3, '0')));
        } while (Board::where('BoardMemberID', $slug)->withTrashed()->exists());

        return $slug;
    }

}
