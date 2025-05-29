<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ThirdParty\SSRSService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

//use function React\Async\await;

class ReportController extends Controller
{
    public function viewSsrsReport(Request $request)
    {
        try {
            // Get the SSRS service instance
            $ssrsService = new SSRSService();

            // Get proxy configuration from environment or config
            //$proxyUrl = config('services.proxy.url', 'http://proxy-server:8080'); &rs:embed=true

            // Get report parameters
            $reportPath = /*$request->input('reportPath',*/
                '/BRERP/Inventory/ItemCatalogue&rs:embed=true';//);
            $reportFormat = 'rs:embed=true';/* $request->input('format','HTML4.0');*/
            //$reportParameters = $request->input('parameters', []);


            /*if (empty($reportPath)) {
                return response()->json(['error' => 'Report path is required'], 400);
            }*/

            // Create a custom Guzzle client with proxy configuration
            $client = new Client([
                'base_uri' => $ssrsService->serverURL,
                'proxy' => $ssrsService->serverURL,//$proxyUrl,
                'auth' => [$ssrsService->getUsername(), $ssrsService->getPassword(), 'ntlm'],
                'verify' => false,
                'cookies' => true,
                'timeout' => 60,
            ]);

            // Build the report URL
            $reportUrl = '/reportserver?' . $reportPath;
            //$queryParams = ['rs:Format' => $reportFormat];
            $queryParams = ['rs:embed' => 'true'];

            // Add custom report parameters if provided
            /* foreach ($reportParameters as $key => $value) {
                 $queryParams[$key] = $value;
             }*/

            // Make the request through the proxy
            $response = $client->get($reportUrl, [
                //'query' => 'rs:embed=true',
                'headers' => [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml',
                    'User-Agent' => $request->header('User-Agent'),
                ],
            ]);

            // Get the content type from the response
            $contentType = $response->getHeaderLine('Content-Type');

            // Return the response with appropriate headers
            return response($response->getBody()->getContents())
                ->header('Content-Type', $contentType);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while loading the report',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
