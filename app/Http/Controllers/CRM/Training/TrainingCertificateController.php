<?php

namespace App\Http\Controllers\CRM\Training;

use App\Http\Controllers\Controller;
use App\Models\CRM\Training\TrainingCertificate;
use Illuminate\Http\Request;

class TrainingCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingCertificate::with(['client', 'program', 'session.program', 'template']);

        $today = now()->toDateString();
        $expiringCutoff = now()->addDays(90)->toDateString();

        if ($request->filled('status')) {
            if ($request->status === 'Expired') {
                $query->whereNotNull('ExpiresOn')->whereDate('ExpiresOn', '<', $today);
            } elseif ($request->status === 'Expiring') {
                $query->whereNotNull('ExpiresOn')
                    ->whereDate('ExpiresOn', '>=', $today)
                    ->whereDate('ExpiresOn', '<=', $expiringCutoff);
            } elseif ($request->status === 'Valid') {
                $query->where(function ($q) use ($expiringCutoff) {
                    $q->whereNull('ExpiresOn')
                      ->orWhereDate('ExpiresOn', '>', $expiringCutoff);
                });
            }
        }

        $certificates = $query->orderByDesc('IssuedOn')->paginate(30);
        $certificates->getCollection()->transform(function ($certificate) use ($today, $expiringCutoff) {
            if (!$certificate->ExpiresOn) {
                $certificate->ComputedStatus = 'Valid';

                return $certificate;
            }

            $expires = $certificate->ExpiresOn->format('Y-m-d');
            if ($expires < $today) {
                $certificate->ComputedStatus = 'Expired';
            } elseif ($expires <= $expiringCutoff) {
                $certificate->ComputedStatus = 'Expiring';
            } else {
                $certificate->ComputedStatus = 'Valid';
            }

            return $certificate;
        });
        $statusList = ['Valid', 'Expiring', 'Expired'];

        return view('crm.training.certificates.index', compact('certificates', 'statusList'));
    }
}
