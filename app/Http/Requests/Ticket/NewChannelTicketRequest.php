<?php

namespace App\Http\Requests\Ticket;

use App\Models\CodeDetail;
use App\Services\StaticListsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class NewChannelTicketRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'ticket_id'          => [
                                         'required',
                                         'string',
                                         'max:100',
                                        ],
                'ticket_title'       => [
                                         'required',
                                         'string',
                                         'max:255',
                                        ],
                'ticket_description' => [
                                         'required',
                                         'string',
                                        ],
                'ticket_category'    => ['required'],
               ];
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
        throw ValidationException::withMessages(['ticket_category' => 'Category may be invalid']);
    }
}
