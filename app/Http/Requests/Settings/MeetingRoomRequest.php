<?php

namespace App\Http\Requests\Settings;

use App\Models\BR\Branch;
use App\Models\MeetingRoom;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MeetingRoomRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'RooMName'     => [
                                   'required',
                                   'string',
                                   'max:255',
                                  ],
                'RooMCapacity' => [
                                   'required',
                                   'integer',
                                   'min:2',
                                  ],
                'RooMNotes'    => [
                                   'nullable',
                                   'string',
                                   'max:2000',
                                  ],
                'RooMBranch'   => [
                                   'nullable',
                                   'string',
                                  ],
               ];
    }

    public function generateID(): string
    {
        $number = MeetingRoom::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::upper(Str::slug('ROOM-' . Str::padLeft(($number), 3, '0')));
        } while (MeetingRoom::where('RoomID', $slug)->withTrashed()->exists());

        return $slug;
    }

    /**
     * @throws ValidationException
     */
    public function getBranch(): ?string
    {
        if (empty($this->validated('RooMBranch'))) {
            return null;
        }

        if (Branch::query()->where('OurBranchID', $this->validated('RooMBranch'))->exists()) {
            return $this->validated('RooMBranch');
        }

        throw ValidationException::withMessages(['RooMBranch' => 'Branch may be invalid']);
    }
}
