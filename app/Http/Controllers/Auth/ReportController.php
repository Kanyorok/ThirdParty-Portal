<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ThirdParty\SSRSService;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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



        // Parse XML to Collection
        $collection = $this->parseReportXml($xmlResponse);
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
}
