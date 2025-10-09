<?php

namespace App\Http\Requests\FleetManagement;

use Illuminate\Foundation\Http\FormRequest;

class FleetVehicleRequestsRequest extends FormRequest
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
            'RequestedBy' => 'required|exists:t_Employees,Id',
            'Department' => 'required|exists:t_Departments,Id',
            'RequestDate' => 'required|date',
            'TripNo' => 'required|exists:t_TripLogs,Id',
            'TripDate' => 'required|date|after_or_equal:today',
            'Purpose' => 'required|string|max:255',
            'FromLocation' => 'required|string|max:255',
            'ToLocation' => 'required|string|max:255',
            'PassengerCount' => 'nullable|integer|min:1',
            'PreferredVehicleType' => 'nullable|string|exists:t_CodeDetails,ID',
            'Status' => 'nullable|string|exists:t_CodeDetails,ID',
            'ApprovedBy' => 'nullable|exists:t_Employees,Id',
            'ApprovedOn' => 'nullable|date|after_or_equal:today',
            'RejectionReason' => 'nullable|string|max:255',
        ];

    }
}
