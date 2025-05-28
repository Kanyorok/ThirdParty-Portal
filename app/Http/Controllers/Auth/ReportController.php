<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ThirdParty\SSRSService;
use Exception;
use Illuminate\Http\Request;
use SoapFault;
use SSRS\Report;

class ReportController extends Controller
{

    public function viewSsrsReport(Request $request)
    {
        $service = new SSRSService();

        try {
            $ssrs = new Report($service->serverURL . 'ReportServer/', [
                'username' => $service->getUsername(),
                'password' => $service->getPassword(),
            ]);


            // Set the report path
            $ssrs->loadReport('/BRERP/Inventory/ItemCatalogue');

            // Set parameters if any
            //$ssrs->setExecutionParameters(['Param1' => 'Value1']);

            // Render the report (e.g., to HTML)
            $output = $ssrs->render('HTML5'); // Or other formats like PDF

            // You might then save this output to a temporary file and display it,
            // or directly embed if it's HTML.
            // For HTML, you could pass it to a view or return it directly.
            return view('reports.details', ['reportHtml' => $output]);
        } catch (SoapFault $sf) {
            dd($sf);
        } catch (Exception $e) {
            // Handle error
            dd($e);
        }
    }
}
