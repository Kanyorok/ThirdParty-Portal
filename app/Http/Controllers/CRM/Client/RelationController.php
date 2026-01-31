<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\BR\SystemCodeDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RelationController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     * @throws \Exception
     */
    public function __invoke(Request $request, Client $client): JsonResponse
    {
        return Datatables::of($client->relations()->with(['type', 'status', 'relation'])->lock('WITH(NOLOCK)')->select('*'))/*->addIndexColumn()*/
        ->editColumn('Mobile', function (Client $client) {
            if (! empty($client->Mobile)) {
                return $client->Mobile;
            }
            if (! empty($client->Phone1)) {
                return $client->Phone1;
            }
            if (! empty($client->Phone2)) {
                return $client->Phone2;
            }

            return ' - ';
        })->editColumn('relation.Description', function (Client $client) {
            return ($client->relation instanceof SystemCodeDetail)
                ? $client->relation->Description
                : '';
        })->editColumn('ClientID', function (Client $client) {
            return '<a href="' . route('clients.show', $client->ClientID) . '">' . $client->ClientID . '</a>';
        })->setRowClass('mouse_pointer user-select-none client-row-data')->setRowData([
                                                                                       'data-url' => function (Client $client) {
                                                                                           return route('clients.show', $client->ClientID);
                                                                                       },
                                                                                      ])->rawColumns(['action', 'ClientID'])->make();
    }
}
