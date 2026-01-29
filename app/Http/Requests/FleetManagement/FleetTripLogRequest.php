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

                 'ParentTripID' => 'nullable|exists:t_TripLogs,Id',
                 'TripType' => 'required|string|exists:t_CodeDetails,ID',
                 'TripCode' => 'nullable|string|max:100',
                 'VehicleType' => 'required|integer|exists:t_CodeDetails,ID',
                 'LoadType' => 'nullable|integer|exists:t_CodeDetails,ID',

                 // Dates & times
                 'TripStartDate' => 'required|date|after_or_equal:today',
                 'StartTime' => 'nullable|date_format:H:i',

                 'TripEndDate' => 'nullable|date|after_or_equal:TripStartDate|after_or_equal:today',
                 'EndTime' => 'nullable|date_format:H:i|after_or_equal:StartTime',

                 // Locations
                 'StartLocation' => 'nullable|string|max:255',
                 'EndLocation' => 'nullable|string|max:255',

                 // Distance & route
                 'DistanceCovered' => 'nullable|integer|min:0',
                 'Route' => 'nullable|integer|exists:t_FleetRoutePlans,Id',

                 // Texts
                 'Purpose' => 'nullable|string|max:255',
                 'Notes' => 'nullable|string',

                 // Child trips
                 'childTrips' => 'nullable|array',
                 'childTrips.*.TripStartDate' => 'required|date|after_or_equal:today',
                 'childTrips.*.StartTime' => 'nullable|date_format:H:i',
                 'childTrips.*.TripEndDate' => 'required|date|after_or_equal:childTrips.*.TripStartDate|after_or_equal:today',
                 'childTrips.*.EndTime' => 'nullable|date_format:H:i|after_or_equal:childTrips.*.StartTime',
                 'childTrips.*.StartLocation' => 'nullable|string|max:255',
                 'childTrips.*.EndLocation' => 'nullable|string|max:255',
                 'childTrips.*.Purpose' => 'nullable|string|max:255',
                 'childTrips.*.Notes' => 'nullable|string',
             ];
    }
}
