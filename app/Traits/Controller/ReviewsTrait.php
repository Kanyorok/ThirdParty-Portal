<?php

namespace App\Traits\Controller;

use App\Models\BR\Branch;
use App\Models\BR\Client;
use App\Models\Lead;
use App\Models\Review;
use App\Services\Feedback\ReviewService;
use App\Services\PartyService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait ReviewsTrait
{
    /**
     * @throws Exception
     */
    private function reviews(Builder|MorphMany $query, array $with = [], array $extra = []): JsonResponse
    {
        if (!empty($with)) {
            $query->with($with);
        }

        return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Review $review) {
                return '<button type="button"  data-click_url="' . route('reviews.show', [$review->Id]) . '" data-summary_title="review details" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('Tonality', function (Review $review) {
                return $review->Tonality->name;
            })->editColumn('CreatedOn', function (Review $review) {
                return $review->CreatedOn?->format('F d, Y h:i A');
            })->editColumn('Party', function (Review $review) use ($extra) {
                if (in_array('Party', $extra, true) && in_array($review->Party, [Client::getPrimaryKey(), Lead::getPrimaryKey()], true)) {
                    return (new PartyService($review->party))->getDTRow();
                }

                return $review->Party;
                /*})->editColumn('party.Name', function (Review $review) {
                    if ()
                    if ($review->party instanceof Model) {

                    }
                    return $review->Party;*/
            })->editColumn('branch.BranchName', function (Review $review) use ($with) {
                if (!in_array('branch', $with, true)) {
                    return '';
                }
                if ($review->branch instanceof Branch) {
                    return $review->branch->BranchName;
                }
                return ' ? ';
            })->editColumn('Rating', function (Review $review) {
                return (new ReviewService($review))->getRate(' width="32" height="32" class="img-thumbnail" alt="' . $review->Rating . ' star"');
            })->setRowClass('user-select-none dbl-click-summary-data')->setRowData([
                'dbl_click_url' => function (Review $review) {
                    return route('reviews.show', [$review->Id]);
                }, 'summary_title' => 'review details',
            ])->rawColumns(['action', 'Rating', 'Party'])->make();
    }
}
