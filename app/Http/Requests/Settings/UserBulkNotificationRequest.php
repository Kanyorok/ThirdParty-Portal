<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserBulkNotificationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'NotificationLabel'   => [
                                          'required',
                                          'string',
                                          'max:255',
                                         ],
                'NotificationContent' => [
                                          'required',
                                          'string',
                                          'max:2000',
                                         ],
               ];
    }
}
