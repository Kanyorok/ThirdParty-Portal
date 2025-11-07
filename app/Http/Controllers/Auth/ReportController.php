<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ThirdParty\SSRSService;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Collection;
use Log;

//use function React\Async\await;

class ReportController extends Controller
{
    public function show()
    {
        $service = new SSRSService();
        # $reportPath = '/BRERP/Inventory/ItemCatalogue';
        $reportPath = '/BRERP/Admin/Users';

        //return $this->parseXmlToCollection( $service->exportReport($reportPath, 'XML'));

        $xmlResponse = $service->exportReport($reportPath, 'XML', content: true);

        /* dd($xmlResponse);;
         // Get the XML content as string
         $xmlString = $xmlResponse->getContent();
         dd($xmlString);*/

        // Parse XML to Collection
        $collection = $this->parseReportXml($xmlResponse);

        dd($collection);

        //dd($report);

        /*  $host = rtrim($service->serverURL, '/');
          $username = $service->getUsername();
          $password = $service->getPassword();

          $url = $host . '?'. http_build_query([
                  'report' => $reportPath,
                  'rs:Command' => 'Render',
                  'rs:Format' => 'HTML4.0'
              ]);

          // Create stream context with basic auth
          $context = stream_context_create([
              'http' => [
                  'header' => 'Authorization: Basic ' . base64_encode($username . ':' . $password)
              ]
          ]);

          $reportHtml = file_get_contents($url, false, $context);

          return view('reports.details', ['reportHtml' => $reportHtml]);*/
    }


    public function parseReportXml(string $xmlString): Collection
    {
        // Suppress XML errors and warnings
        libxml_use_internal_errors(true);

        try {
            // Create a new DOM document
            $dom = new DOMDocument('1.0', 'UTF-8');

            // Load the XML string
            $dom->loadXML($xmlString);

            // Create a new XPath object
            $xpath = new DOMXPath($dom);

            // Register the namespaces
            $xpath->registerNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');

            // The default namespace is trickier - we need to give it a prefix
            // Find default namespace from the document root
            $root = $dom->documentElement;
            if ($root && $root->hasAttribute('xmlns')) {
                $defaultNs = $root->getAttribute('xmlns');
                $xpath->registerNamespace('ns', $defaultNs);
            }

            // Find all Details elements - using namespace-aware query
            $detailsNodes = $xpath->query('//ns:Details');

            // If no nodes found, try without namespace
            if (!$detailsNodes || $detailsNodes->length === 0) {
                $detailsNodes = $xpath->query('//Details');
            }

            // Create a new collection to hold our results
            $collection = collect();

            // Process each Details node
            if ($detailsNodes) {
                foreach ($detailsNodes as $node) {
                    $item = [];

                    // Get all attributes
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attr) {
                            $item[$attr->nodeName] = $attr->nodeValue;
                        }
                    }

                    // Add item to collection
                    $collection->push($item);
                }
            }

            // Clear XML errors
            libxml_clear_errors();

            return $collection;
        } catch (Exception $e) {
            // Log the error
            Log::error('XML Parsing Error: ' . $e->getMessage());

            // Clear XML errors
            libxml_clear_errors();

            // Return empty collection
            return collect();
        }
    }


    /*public function parseXmlToCollection($xmlString)
    {
        // Remove BOM character if present
        $xmlString = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $xmlString);
dd($xmlString);
        libxml_use_internal_errors(true);

        // Load the XML
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML("<?xml version="1.0" encoding="utf-8"?><Report xsi:schemaLocation="Users http://db-server:7092/ReportServer?%2FBRERP%2FAdmin%2FUsers&amp;rs%3AFormat=XML&amp;rc%3ASchema=True" Name="Users" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="Users"><Tablix1><Details_Collection><Details UserID="ERPSYS" Name="SYSTEM" Email="SYSTEM ACCOUNT" Phone="0" /><Details UserID="CSADM" Name="User Default" Email="admin@test.co.ke" Phone="254700100100" /><Details UserID="CMCKNIGHT" Name="Mcknight Chelsea" Email="minely@mailinator.com" Phone="+1 (285) 648-6658" /><Details UserID="FSALINAS" Name="Salinas Fuller" Email="fihew@mailinator.com" Phone="+254 (700) 526-855" /><Details UserID="LNGUYEN" Name="Nguyen Lila" Email="wuzu@mailinator.com" Phone="+254 (700) 365-201" /></Details_Collection></Tablix1></Report>");

        $xml = simplexml_import_dom($dom);

        // Clear any errors
        libxml_clear_errors();



        // Check if XML was parsed successfully
        if (!$xml) {

            return collect();
        }

        $collection = collect();

        // Find all Details elements in the XML (works for both report types)
        $detailsElements = $xml->xpath('//Details');

        // Convert each Details element and its attributes to an array item in the collection
        foreach ($detailsElements as $details) {
            // Convert attributes to array
            $attributes = [];
            foreach ($details->attributes() as $key => $value) {
                $attributes[$key] = (string)$value;
            }

            $collection->push($attributes);
        }

        return $collection;
    }*/

}
