<?php

namespace App\Http\Requests\Base;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\Communication\Email;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class MailToRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'mail_to'       => [
                                    'required',
                                    'email:rfc,dns',
                                    'max:200',
                                   ],
                'mail_cc'       => [
                                    'nullable',
                                    'array',
                                    'max:20',
                                   ],
                'mail_subject'  => [
                                    'required',
                                    'string',
                                    'max:200',
                                   ],
                'mail_content'  => [
                                    'required',
                                    'string',
                                    'min:5',
                                   ],
                'mail_reply_to' => [
                                    'nullable',
                                    'string',
                                   ],
               ];
    }

    /**
     * @throws ValidationException
 *
* public function getUsers(): array
    * {
        * $users = $this->validated('mail_users');
        * if (!is_array($users)) {
            * return [];
        * }
        * $ccEmails = collect();
        * $id = 1;
        * foreach ($users as $userId) {
            * $user = User::query()->where('UserID', $userId)->where('t_Users.UserID', '!=', SystemHelper::ID)->first(['Id', 'UserID', 'Email']);
            * if ($user instanceof User) {
                * if (!filter_var($user->Email, FILTER_VALIDATE_EMAIL)) {
                    * throw ValidationException::withMessages([
                        * 'mail_users' => $user->UserID . ' does not have a valid email address.',
                    * ]);
                * }
                * $ccEmails->add([$user->Email]);
                * $id++;
                * continue;
            * }
 *
* throw ValidationException::withMessages([
                * 'mail_users' => $user->UserID . 'The mail address ' . $id . ' is not a valid email address.',
            * ]);
        * }
 *
     * return $ccEmails->toArray();
     * } */

    public function messages(): array
    {
        return ['mail_content.min' => 'Write something about it.'];
    }

    public function getReplyTo(): Email
    {
        $mail = Email::query()->where('EmailID', $this->validated('mail_reply_to'))->first();
        if ($mail instanceof Email) {
            return $mail;
        }

        throw ValidationException::withMessages(['mail_reply_to' => 'reply to email invalid, maybe deleted']);
    }


    /**
     * @throws ValidationException
     */
    public function getLeadEmail(Lead $lead): string
    {
        $mail = $this->validated('mail_to');

        if ($lead->Email === $mail) {
            return $mail;
        }

        //check contact.
        if ($lead->contacts()->where('t_Contacts.Email', $mail)->exists()) {
            return $mail;
        }

        throw ValidationException::withMessages(['mail_to' => 'The mail address does not related to lead.']);
    }

    public function getContactEmail(Contact $contact): string
    {
        $mail = $this->validated('mail_to');

        if ($contact->Email === $mail) {
            return $mail;
        }

        throw ValidationException::withMessages(['mail_to' => 'The mail address does not related to contact.']);
    }

    /**
     * @throws ValidationException
     */
    public function getClientEmail(Client $client): string
    {
        $mail = $this->validated('mail_to');

        if ($client->Email === $mail) {
            return $mail;
        }

        //check contact.
        if ($client->contacts()->where('t_Contacts.Email', $mail)->exists()) {
            return $mail;
        }

        throw ValidationException::withMessages(['mail_to' => 'The mail address does not related to lead.']);
    }

    /**
     * @throws ValidationException
     */
    public function getEmail(): string
    {
        return $this->validated('mail_to');
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
    /**
     * @throws ValidationException
 *
* public function cc(): array
    * {
        * $ccEmails = collect();
        * $cc = $this->validated('mail_cc');
        * if (!is_string($cc)) {
            * return [];
        * }
 *
* $emails = array_map('trim', explode(',', $cc));
        * $id = 1;
        * foreach ($emails as $email) {
            * if (empty($email)) {//because of last comma
                * continue;
            * }
            * if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                * throw ValidationException::withMessages([
                    * 'mail_cc' => 'The mail address ' . $id . ' is not a valid email address.',
                * ]);
            * }
            * $ccEmails->add([$email]);
     * $id++;
     * }
     *
     * return $ccEmails->toArray();
    * }  */
}
