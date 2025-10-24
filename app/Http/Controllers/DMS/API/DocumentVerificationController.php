<?php

namespace App\Http\Controllers\DMS\API;

use App\Enums\Core\DataTypesEnum;
use App\Enums\DMS\DocumentValidationTypeEnum;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\DMS\DocumentValidation;
use App\Services\DMS\Files\FileProperties;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class DocumentVerificationController extends Controller
{
    //
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'member_name' => 'required|string',
            'branch_id' => 'required|string|',
            'application_id' => 'required|string',
            'mobile_number' => 'nullable|string',
            'member_class' => 'required|string',
            'email_address' => 'nullable',
            'identification_number' => 'required|string',
            'gender' => 'required',
            'date_of_birth' => 'required|date',
            'captured_by' => 'required|string',
            'status' => 'required|string',
            'member_status' => 'nullable|string',
            'member_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (DocumentValidation::query()->where('Type', DocumentValidationTypeEnum::ClientOnboarding->value)
            ->where('t_DocumentValidation.ValidationId', $validated['application_id'])
            ->exists()) {
            return $this->errored('Application ID already exists.', data: ['status' => '999']);
        }

        $actor = SystemHelper::user();
        try {
            return DB::transaction(function () use ($actor, $validated) {
                $validation = DocumentValidation::create([
                    "Name" => $validated['member_name'],
                    "ValidationId" => $validated['application_id'],
                    "Type" => DocumentValidationTypeEnum::ClientOnboarding->value,
                    "DocumentId" => null,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($validation)->event('create')->log('Created document validation : ' . $validation->ValidationId);
                $dated = now();
                $data = collect();
                $fields = collect();

                foreach ($validated as $item => $value) {
                    if (is_null($value) or strtolower($value) === 'null') {
                        continue;
                    }
                    $type = DataTypesEnum::String;
                    if (Str::of($item)->contains('date')) {
                        try {
                            $value = Carbon::parse($value)->format(FileProperties::DATE_TIME_FORMAT);
                            $type = DataTypesEnum::DateTime;
                        } catch (Exception) {
                        }
                    }

                    $data->add([
                        "Name" => $item,
                        "Value" => $value,
                        "DataType" => $type->value,
                        "DocumentValidationId" => $validation->Id,
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => $dated,
                        'ModifiedOn' => $dated,
                    ]);

                    $fields->add([
                        "FieldName" => strtoupper($item),
                        "Item" => $value,
                        "ItemElementName" => $type->name
                    ]);
                }

                DB::table('t_DocumentValidationAttributes')->insert($data->toArray());

                return $this->succeeded('document validation created successfully', data: [
                    'status' => '000',
                    "Fields" => $fields,
                ]);

            });
        } catch (Throwable $e) {
            Log::error('Saving data failed ' . $e);
        }

        return $this->errored('an unexpected error occurred', ['status' => '999']);
    }
}
