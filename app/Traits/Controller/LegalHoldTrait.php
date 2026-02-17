<?php

namespace App\Traits\Controller;

use App\Enums\DMS\LegalHoldStatusEnum;
use App\Models\DMS\LegalHold;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait LegalHoldTrait
{
    /**
     * @throws Exception
     */
    public function getLegalHolds(Builder|BelongsToMany $query, array $with = [], array $counts = []): JsonResponse
    {
        if (! empty($with)) {
            $query->with($with);
        }
        if (! empty($counts)) {
            $query->withCount($counts);
        }

        return Datatables::of($query->lock('WITH(NOLOCK)'))->addIndexColumn()
            ->addColumn('action', function (LegalHold $legalHold) {
                return '<a  href="' . route('legal-hold.show', [$legalHold->Ref]) . '"  class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
            })->addColumn('documents_count', function ($legalHold) use ($counts) {
                return (in_array('documents', $counts, true)) ? number_format($legalHold->documents_count) : '';
            })->editColumn('CreatedOn', function (LegalHold $legalHold) {
                return $legalHold->CreatedOn?->format('F d, Y h:i A');
            })->editColumn('Status', function (LegalHold $legalHold) {
                return match ($legalHold->Status->value) {
                    LegalHoldStatusEnum::Active->value => '<span class="badge rounded-pill bg-success">Active</span>',
                    LegalHoldStatusEnum::Canceled->value => '<details><summary><span class="badge rounded-pill bg-warning text-dark">Canceled</span></summary>
                            <p>Cancelled On: ' . $legalHold->ReleasedOn?->format('F d, Y h:i A') . '</p></details>',
                    LegalHoldStatusEnum::Released->value => '<details><summary><span class="badge rounded-pill bg-primary">Released</span></summary>
                            <p>Released On: ' . $legalHold->ReleasedOn?->format('F d, Y h:i A') . '</p></details>',
                };
            })->rawColumns(['action', 'Status'])->make();
    }
}
