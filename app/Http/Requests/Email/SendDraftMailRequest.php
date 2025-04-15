<?php

namespace App\Http\Requests\Email;

use App\Helpers\SystemHelper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendDraftMailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'mail_cc'      => [
                                   'nullable',
                                   'array',
                                   'max:20',
                                  ],
                'mail_content' => [
                                   'required',
                                   'string',
                                   'min:5',
                                  ],
               ];
    }

    public function messages(): array
    {
        return ['mail_content.min' => 'Write something about it.'];
    }


    public function getCarbonCopyEmails(): array
    {
        $cc = $this->validated('mail_cc');
        if (!is_array($cc)) {
            return [];
        }
        $valid = [];
        foreach ($cc as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $email;
            }
            //check if a user
            $user = User::query()->where('UserID', $email)->where('t_Users.UserID', '!=', SystemHelper::ID)->first(['Id', 'UserID', 'Email']);
            if (($user instanceof User) && filter_var($user->Email, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $user->Email;
            }
        }
        return $valid;
    }
}
