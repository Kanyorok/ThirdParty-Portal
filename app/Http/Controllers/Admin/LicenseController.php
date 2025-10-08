<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Licensing\LicensingService;
use App\Models\Licensing\License;
use App\Models\Licensing\Instance;
use App\Models\Licensing\LicenseAudit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    private LicensingService $licensingService;

    public function __construct(LicensingService $licensingService)
    {
        $this->licensingService = $licensingService;
        
        // Only allow admin users to access licensing management
        $this->middleware(['auth']);
        
        // Check permissions in each method instead of constructor
        // This avoids middleware registration issues
    }

    /**
     * Check if user has admin permissions
     */
    private function checkAdminPermission(): void
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(403, 'Authentication required for licensing management.');
        }
        
        // Check if user has admin role using Spatie Laravel Permission
        try {
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return; // User has admin role
            }
        } catch (\Exception $e) {
            // Fallback if role system isn't working
        }
        
        // Alternative check: if user has specific permissions or is superuser
        try {
            if (method_exists($user, 'can') && $user->can('manage-licensing')) {
                return; // User has specific permission
            }
        } catch (\Exception $e) {
            // Continue to fallback
        }
        
        // Fallback: Check if user ID is 1 (typically first admin user)
        if ($user->id === 1 || $user->Id === 1) {
            return; // First user is typically admin
        }
        
        abort(403, 'Admin role or permissions required for licensing management.');
    }

    /**
     * Display licensing status and management interface
     */
    public function index()
    {
        $this->checkAdminPermission();
        $status = $this->licensingService->getStatus();
        $instance = Instance::current();
        $recentAudits = LicenseAudit::orderBy('EventAt', 'desc')
            ->take(10)
            ->get();
        $allLicenses = License::orderBy('CreatedOn', 'desc')
            ->take(5)
            ->get();

        return view('admin.licensing.index', compact(
            'status',
            'instance', 
            'recentAudits',
            'allLicenses'
        ));
    }

    /**
     * Show license upload form
     */
    public function create()
    {
        $this->checkAdminPermission();
        $instance = Instance::current();
        return view('admin.licensing.create', compact('instance'));
    }

    /**
     * Upload and import a new license
     */
    public function store(Request $request)
    {
        $this->checkAdminPermission();
        $validator = Validator::make($request->all(), [
            'license_file' => 'required|file|mimes:json,txt|max:10240', // 10MB max
            'public_key_id' => 'required|string|max:64'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('license_file');
            $content = file_get_contents($file->getRealPath());
            $licenseData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            // Validate license structure
            if (!isset($licenseData['payload'], $licenseData['signature'])) {
                throw new \InvalidArgumentException('Invalid license file format');
            }

            $payload = is_string($licenseData['payload']) 
                ? $licenseData['payload'] 
                : json_encode($licenseData['payload']);
                
            $signature = $licenseData['signature'];
            
            // Extract license ID from payload
            $payloadData = json_decode($payload, true);
            $licenseId = $payloadData['license_id'] ?? 'UNKNOWN-' . time();

            // Import the license
            $success = $this->licensingService->importLicense(
                $licenseId,
                $payload,
                $signature,
                $request->input('public_key_id')
            );

            if ($success) {
                return redirect()->route('admin.licensing.index')
                    ->with('success', 'License uploaded and validated successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'License validation failed. Please check the license file and try again.')
                    ->withInput();
            }

        } catch (\JsonException $e) {
            return redirect()->back()
                ->with('error', 'Invalid JSON format in license file.')
                ->withInput();
        } catch (\Exception $e) {
            Log::error('License upload failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'License upload failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display license details
     */
    public function show(License $license)
    {
        $payload = $license->parsed_payload;
        return view('admin.licensing.show', compact('license', 'payload'));
    }

    /**
     * Revoke a license
     */
    public function destroy(License $license)
    {
        $this->checkAdminPermission();
        try {
            $license->update(['Status' => 0]);
            
            LicenseAudit::logEvent(
                LicenseAudit::EVENT_LICENSE_REVOKED,
                'License revoked by admin',
                $license->LicenseId
            );

            $this->licensingService->invalidateCache();

            return redirect()->route('admin.licensing.index')
                ->with('success', 'License revoked successfully.');

        } catch (\Exception $e) {
            Log::error('License revocation failed', [
                'license_id' => $license->LicenseId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to revoke license: ' . $e->getMessage());
        }
    }

    /**
     * Generate instance information for license request
     */
    public function instanceInfo()
    {
        $instance = Instance::current();
        
        $info = [
            'db_guid' => $instance->DbGuid,
            'host_fingerprint' => $instance->HostFingerprint,
            'app_version' => config('app.version', '1.0.0'),
            'generated_at' => now()->toISOString(),
        ];

        return response()->json($info);
    }

    /**
     * Download instance information for license request
     */
    public function downloadInstanceInfo()
    {
        $instance = Instance::current();
        
        $info = [
            'db_guid' => $instance->DbGuid,
            'host_fingerprint' => $instance->HostFingerprint,
            'app_version' => config('app.version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_info' => [
                'hostname' => gethostname(),
                'os' => PHP_OS_FAMILY,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ],
            'generated_at' => now()->toISOString(),
            'requested_by' => auth()->user()->Name ?? auth()->user()->email,
        ];

        $filename = 'instance_info_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($info)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    /**
     * Force license cache refresh
     */
    public function refresh()
    {
        $this->checkAdminPermission();
        try {
            $this->licensingService->invalidateCache();
            $status = $this->licensingService->verifyAndLoad();
            
            return redirect()->route('admin.licensing.index')
                ->with('success', 'License cache refreshed. Status: ' . 
                    ($status->isValid() ? 'Valid' : 'Invalid - ' . $status->getError()));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to refresh license: ' . $e->getMessage());
        }
    }

    /**
     * Show audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = LicenseAudit::query()->orderBy('EventAt', 'desc');
        
        if ($request->filled('event')) {
            $query->where('Event', $request->input('event'));
        }
        
        if ($request->filled('license_id')) {
            $query->where('LicenseId', 'like', '%' . $request->input('license_id') . '%');
        }

        $audits = $query->paginate(50);
        $events = LicenseAudit::distinct()->pluck('Event');

        return view('admin.licensing.audit', compact('audits', 'events'));
    }
}
