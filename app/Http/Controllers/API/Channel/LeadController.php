<?php

namespace App\Http\Controllers\API\Channel;

use App\Enums\LeadTypeEnum;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    /**
     * Handle the incoming request.
     * @throws ValidationException
     */
    public function company(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                                        'name'         => [
                                                           'required',
                                                           'string',
                                                           'max:250',
                                                          ],
                                        'website'      => [
                                                           'nullable',
                                                           'string',
                                                           'url:http,https',
                                                           'active_url',
                                                           'max:250',
                                                          ],
                                        'phone'        => [
                                                           'required',
                                                           'string',
                                                           'max:15', /*'unique:App\Models\Lead,Phone'*/
                                                          ],
                                        'email'        => [
                                                           'nullable',
                                                           'email:rfc,dns',
                                                           'max:250',/* 'unique:App\Models\Lead,Email'*/
                                                          ],
                                        'last_contact' => [
                                                           'nullable',
                                                           'date_format:"Y-m-d H:i"',
                                                           'before:now',
                                                          ],
                                        'notes'        => [
                                                           'nullable',
                                                           'string',
                                                           'max:5000',
                                                          ],
                                       ]);
        } catch (ValidationException $e) {
            return $this->br_response(422, $e->getMessage(), $e->errors());
        }

        if (Lead::query()->where('Phone', $data['phone'])->exists()) {
            return $this->br_response(422, 'lead has already been created and will be contacted.', ['phone' => 'phone number already exists.']);
        }

        $email = ($request->has('email') && Lead::query()->where('Email', $data['email'])->exists()) ? null : $data['email'];

        if ($request->has('last_contact')) {
            try {
                $last_contacted = Carbon::createFromFormat('Y-m-d H:i', $data['last_contact']);
            } catch (Exception) {
                return $this->br_response(422, 'invalid date format provided.', ['last_contact' => 'invalid date format provided.']);
            }
        } else {
            $last_contacted = Carbon::now()->subMinute();
        }

        $actor = SystemHelper::user();
        try {
            DB::transaction(static function () use ($email, $data, $actor, $last_contacted) {
                Lead::create([
                              "Name"                  => $data['name'],
                              "Email"                 => $email,
                              "Phone"                 => $data['phone'],
                              "Website"               => $data['website'],
                              "Notes"                 => $data['notes'],
                              "Type"                  => LeadTypeEnum::Company->value,
                              'ModifiedBy'            => $actor->Id,
                              'CreatedBy'             => $actor->Id,
                              "LastContacted"         => $last_contacted,
                              "RelationshipManagerID" => $actor->Id,
                              'CreatedOn'             => now(),
                              'UpdatedOn'             => now(),
                             ]);
            });
        } catch (Exception $e) {
            Log::error('Error saving company lead from channel : ' . $e->getMessage());
            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->br_response('000', 'Lead created successfully');
    }

    /**
     * Handle the incoming request.
     * @throws ValidationException
     */
    public function individual(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                                        'first_name'   => [
                                                           'required',
                                                           'string',
                                                           'max:250',
                                                          ],
                                        'surname'      => [
                                                           'required',
                                                           'string',
                                                           'max:150',
                                                          ],
                                        'phone'        => [
                                                           'required',
                                                           'string',
                                                           'max:15',/* 'unique:App\Models\Lead,Phone'*/
                                                          ],
                                        'email'        => [
                                                           'nullable',
                                                           'email:rfc,dns',
                                                           'max:250',/*, 'unique:App\Models\Lead,Email'*/
                                                          ],
                                        'job_title'    => [
                                                           'nullable',
                                                           'string',
                                                           'max:200',
                                                          ],
                                        'last_contact' => [
                                                           'nullable',
                                                           'date_format:"Y-m-d H:i"',
                                                           'before:now',
                                                          ],
                                        'notes'        => [
                                                           'nullable',
                                                           'string',
                                                           'max:5000',
                                                          ],
                                       ]);
        } catch (ValidationException $e) {
            return $this->br_response(422, $e->getMessage(), $e->errors());
        }

        if (Lead::query()->where('Phone', $data['phone'])->exists()) {
            return $this->br_response(422, 'lead has already been created and will be contacted.', ['phone' => 'phone number already exists.']);
        }

        $email = ($request->has('email') && Lead::query()->where('Email', $data['email'])->exists()) ? null : $data['email'];

        if ($request->has('last_contact')) {
            try {
                $last_contacted = Carbon::createFromFormat('Y-m-d H:i', $data['last_contact']);
            } catch (Exception) {
                return $this->br_response(422, 'invalid date format provided.', ['last_contact' => 'invalid date format provided.']);
            }
        } else {
            $last_contacted = Carbon::now()->subMinute();
        }


        $actor = SystemHelper::user();
        try {
            DB::transaction(static function () use ($email, $data, $last_contacted, $actor) {
                Lead::create([
                              "Name"                  => $data['first_name'],
                              "OtherNames"            => $data['surname'],
                              "JobTitle"              => $data['job_title'],
                              "Email"                 => $email,
                              "Phone"                 => $data['phone'],
                              "Notes"                 => $data['notes'],
                              "Type"                  => LeadTypeEnum::Individual->value,
                              'ModifiedBy'            => $actor->Id,
                              'CreatedBy'             => $actor->Id,
                              "LastContacted"         => $last_contacted,
                              "RelationshipManagerID" => $actor->Id,
                              'CreatedOn'             => now(),
                              'UpdatedOn'             => now(),
                             ]);
            });
        } catch (Exception $e) {
            Log::error('Error saving individual lead from channel : ' . $e->getMessage());
            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->br_response('000', 'Lead created successfully');
    }
}
