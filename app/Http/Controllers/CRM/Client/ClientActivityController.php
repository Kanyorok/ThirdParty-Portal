<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ClientActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Client $client): View
    {
        $last = ($request->last_view) ?? 9223372036854775807;//can replace with max

        return view('crm.clients.snippets.activities')//todo fix this with simple paginate
            ->with('activities', $client->activities()->where('ActivityID', '<', $last)->latest('ActivityID')->limit(10)->get())
            ->with('activity_more', $client->activities()->where('ActivityID', '<', $last)->latest('ActivityID')->count());
    }
}
