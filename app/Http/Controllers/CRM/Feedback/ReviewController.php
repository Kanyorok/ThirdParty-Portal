<?php

namespace App\Http\Controllers\CRM\Feedback;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use App\Models\CRM\Review;
use App\Traits\Controller\ReviewsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    use ReviewsTrait;

    public function __construct()
    {
        $this->middleware('ajax')->only('show');
        $this->authorizeResource(Review::class);
    }

    /**
     * List of feedbacks.
     * @throws \Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->reviews(Review::query(), ['branch'], ['Party']);
        }

        return view('crm.feedback.reviews.index');
    }

    /**
     * Summary of the same.
     */
    public function show(Request $request, Review $review): View
    {
        $party = null;
        if (in_array($review->Party, [Client::getPrimaryKey(), Lead::getPrimaryKey()], true)) {
            $party = $review->party;
        }
        activity()->causedBy($request->user())->performedOn($review)->event('view')->log('Viewed review details');
        return view('crm.feedback.reviews.show', compact('review', 'party'));
    }
}
