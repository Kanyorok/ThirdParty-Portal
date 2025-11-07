<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Licensing\LicenseRecord;
use App\Services\Licensing\LicensingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
	public function index(LicensingService $licensing)
	{
		$license = $licensing->current();
		$records = LicenseRecord::query()->orderByDesc('Id')->limit(10)->get();
		return view('admin.licensing.index', compact('license','records'));
	}

	public function store(Request $request, LicensingService $licensing)
	{
		$validated = $request->validate([
			'payload' => ['required','string'],
			'signature' => ['required','string'],
			'license_id' => ['required','string','max:64'],
			'public_key_id' => ['required','string','max:64'],
		]);

		DB::transaction(function () use ($validated) {
			LicenseRecord::create([
				'LicenseId' => $validated['license_id'],
				'PayloadJson' => $validated['payload'],
				'SignatureBase64' => $validated['signature'],
				'PublicKeyId' => $validated['public_key_id'],
				'Status' => 1,
				'CreatedOn' => now('UTC')->toDateTimeString(),
			]);
		});

		$licensing->invalidateCache();
		$result = $licensing->current();
		if (!$result->isValid()) {
			return back()->with('error', 'License saved but invalid: '.($result->reason ?? 'unknown'));
		}
		return back()->with('success', 'License uploaded and validated.');
	}
}