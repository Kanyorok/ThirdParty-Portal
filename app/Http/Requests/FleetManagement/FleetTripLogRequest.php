<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetTripLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

    return [
            'VehicleID' => 'required|exists:t_FleetVehicles,Id',
            'DriverType' =>'required|exists:t_CodeDetails,ID',
            'DriverID' => 'required|integer',
            'TripStartDate' => 'required|date',
            'StartTime' => 'nullable|date_format:H:i',
            'TripEndDate' => 'required|date',
            'EndTime' => 'nullable|date_format:H:i|after_or_equal:StartTime',
            'StartLocation' => 'nullable|string|max:255',
            'EndLocation' => 'nullable|string|max:255',
            'DistanceCovered' => 'nullable|integer|min:0',
            'Route' => 'nullable|integer|exists:t_TripLogs,Id',
            'Purpose' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
    ];

    }
}
