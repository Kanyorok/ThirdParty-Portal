<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceDevice;
use Illuminate\Http\Request;

class AttendanceDeviceController extends Controller
{
    public function index()
    {
        $devices = AttendanceDevice::orderBy('Name')->paginate(20);

        return view('hr.attendance.devices.index', compact('devices'));
    }

    public function create()
    {
        return view('hr.attendance.devices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'DeviceCode' => 'required|string|max:100|unique:t_HRAttendanceDevices,DeviceCode',
            'Name' => 'required|string|max:150',
            'Channel' => 'required|string|max:50',
            'AllowedIPs' => 'nullable|string|max:500',
            'AllowedLocations' => 'nullable|string|max:500',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive', true);
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        AttendanceDevice::create($data);

        return redirect()->route('hr.attendance.devices.index')->with('success', 'Device saved.');
    }

    public function edit($id)
    {
        $device = AttendanceDevice::findOrFail($id);

        return view('hr.attendance.devices.edit', compact('device'));
    }

    public function update(Request $request, $id)
    {
        $device = AttendanceDevice::findOrFail($id);
        $data = $request->validate([
            'Name' => 'required|string|max:150',
            'Channel' => 'required|string|max:50',
            'AllowedIPs' => 'nullable|string|max:500',
            'AllowedLocations' => 'nullable|string|max:500',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive', true);
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();
        $device->update($data);

        return redirect()->route('hr.attendance.devices.index')->with('success', 'Device updated.');
    }
}
