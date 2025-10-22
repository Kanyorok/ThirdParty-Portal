<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Settings\APICredential;
use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Http;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;
use stdClass;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSRSService
{
    protected PendingRequest $_query;

    protected string $_serverAPIUrl;
    public string $serverURL;
    protected string $_cookiePath;


    protected string $_username;
    protected string $_password;

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        $ssrs = APICredential::query()->where('Integration', IntegrationsEnum::ReportService->value)->latest('Id')->first();
        if (!$ssrs instanceof APICredential) {
            throw new ErroredException('there are no report service configuration');
        }

        $ssrsConfig = $ssrs?->Configuration;
        if (!$ssrsConfig instanceof stdClass) {
            throw new ErroredException('invalid report service configuration');
        }

        if (!property_exists($ssrsConfig, 'password') || !property_exists($ssrsConfig, 'username') || !property_exists($ssrsConfig, 'host') || !property_exists($ssrsConfig, 'path')) {
            throw new ErroredException('invalid report service configuration');
        }

        try {
            $password = Crypt::decryptString($ssrsConfig->password);
        } catch (DecryptException) {
            throw new ErroredException('invalid report service configuration');
        }
        $path = strtolower($ssrsConfig->path);
        $username = $ssrsConfig->username;
        $this->_username = $username;
        $this->_password = (string)$password;
        $this->serverURL = $ssrsConfig->host;
        $this->_serverAPIUrl = Str::rtrim($this->serverURL, '/') . "/{$path}/api/v2.0/";

        $this->_query = Http::withCookies(request()->cookie(), parse_url($this->serverURL, PHP_URL_HOST))
            ->withHeaders(request()->header())->retry(3, 100)->timeout(60 * 10)
            ->withBasicAuth($username, $password)->withOptions(['auth' => [$username, $password, 'ntlm']]);

    }

    public function getPassword(): string
    {
        return $this->_password;
    }

    /**
     * @throws GuzzleException
     */
    public function initiateRequest(string $path): ResponseInterface

    {
        $cookieJar = new FileCookieJar($this->_getPath(), true);
        $client = new Client([
            'base_uri' => $this->serverURL,
            'auth' => [$this->_username, $this->_password, 'ntlm'],
            'cookies' => $cookieJar,
            'verify' => false, // disable SSL verification if needed
        ]);
        $response = $client->get($path);

        $cookieJar->save($this->_getPath());
        return $response;
    }

    /**
     * @throws GuzzleException
     */
    public function cookieRequest(string $path): ResponseInterface
    {
        $cookieJar = new FileCookieJar($this->_getPath(), true);

        $client = new Client([
            'base_uri' => $this->serverURL,
            'cookies' => $cookieJar,
        ]);

        return $client->get($path);
    }

    private function _getPath(): string
    {
        $this->_cookiePath = storage_path('app/cookies/ntlm_cookies.json');
        return $this->_cookiePath;
    }

    public function getQuery(bool $wilAuth = false): PendingRequest
    {
        /* if ($wilAuth){
             return $this->_query->withBasicAuth($this->_username, $this->_password)->withOptions(['auth' => [$this->_username, $this->_password, 'ntlm']]);
         }*/
        return $this->_query;
    }

    public static function testConfig(string $Host, string $Path, string $username, #[SensitiveParameter] string $password): ?string
    {
        try {
            $query = Http::withBasicAuth($username, $password)->withOptions(['auth' => [$username, $password, 'ntlm']])
                ->get(Str::of($Host)->trim()->rtrim('/') . "/{$Path}/api/v2.0/ME");
            //->get(Str::of($Host)->trim()->rtrim('/') . "/reports/api/v2.0/ME");
        } catch (ConnectionException|Exception) {
            return null;
        }
        if ($query->successful() && array_key_exists('DisplayName', $query->json())) {
            return $query->json()['DisplayName'];
        }
        return null;
    }

    public static function queryParams(array $parameters): string
    {
        $params = '';
        foreach ($parameters as $index => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $params .= "&$index=$val";
                }
            } else {
                $params .= "&$index=$value";
            }
        }
        return Str::of($params)->trim()->toString();
    }

    /**
     * @throws ConnectionException
     */
    public function exportReport(string $path, array $parameters = [], string $format = 'XML', bool $content = false): StreamedResponse|string
    {
        $response = $this->_query
            ->get(Str::rtrim($this->serverURL, '/') . "/ReportServer?" . $path . "&rs:Format=$format" . self::queryParams($parameters));

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to export report. Status: {$response->status()}"
            );
        }
        if ($content) {
            return $response->body();
        }

        // Determine content type based on format
        $contentType = $this->getContentType($format);
        $extension = $this->getFileExtension($format);

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, basename($path) . $extension, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment',
        ]);
    }


    /**
     * @throws ErroredException
     */
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

            // Create a new collection to hold our results
            $collection = collect();

            // Try different patterns for detail elements
            $detailsPatterns = [
                '//ns:Details',      // Standard Details with namespace
                '//ns:Details1',     // Details1 with namespace
                '//Details',         // Standard Details without namespace
                '//Details1',        // Details1 without namespace
                '//*[starts-with(local-name(), "Details")]' // Any element starting with "Details"
            ];

            $detailsNodes = null;
            foreach ($detailsPatterns as $pattern) {
                $detailsNodes = $xpath->query($pattern);
                if ($detailsNodes && $detailsNodes->length > 0) {
                    break;
                }
            }

            // Process each Details node
            if ($detailsNodes && $detailsNodes->length > 0) {
                foreach ($detailsNodes as $node) {
                    $item = [];

                    // Get all attributes
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attr) {
                            $item[$attr->nodeName] = $attr->nodeValue;
                        }
                    }

                    // Add item to collection if it has any attributes
                    if (!empty($item)) {
                        $collection->push($item);
                    }
                }
            }

            // Clear XML errors
            libxml_clear_errors();

            return $collection;
        } catch (Exception $e) {
            // Log::error('XML Parsing Error: ' . $e->getMessage());
            libxml_clear_errors();
            throw new ErroredException('Failed to parse report ');
        }
    }


    /**
     * Get content type based on export format
     */
    protected function getContentType(string $format): string
    {
        return match (strtoupper($format)) {
            'PDF' => 'application/pdf',
            'EXCEL' => 'application/vnd.ms-excel',
            'WORD' => 'application/msword',
            'HTML4.0' => 'text/html',
            'XML' => 'application/xml',
            'IMAGE' => 'image/tiff',
            'CSV' => 'text/csv',
            default => 'application/octet-stream',
        };
    }

    // Example usage with device info

    /**
     * Get file extension based on export format
     */
    protected function getFileExtension(string $format): string
    {
        return match (strtoupper($format)) {
            'PDF' => '.pdf',
            'EXCEL', 'EXCELOPENXML' => '.xls',
            'WORD' => '.doc',
            'HTML4.0' => '.html',
            'XML' => '.xml',
            'CSV' => '.csv',
            'IMAGE' => '.tiff',
            default => '',
        };
    }

    /**
     * Fetch report data in specified format
     *
     * @param string $path Report path
     * @param array $parameters Report parameters
     * @param string $format Output format (CSV, JSON, XML, EXCEL)
     * @return array
     * @throws ConnectionException
     */
    public function fetchReport(string $path, array $parameters = [], string $format = 'JSON'): array
    {
        // Build parameters string
        $paramString = '';
        if (!empty($parameters)) {
            $params = [];
            foreach ($parameters as $key => $value) {
                $params[] = "$key=$value";
            }
            $paramString = '&' . implode('&', $params);
        }

        $response = $this->_query
            ->get($this->_serverAPIUrl . "Reports(Path='$path')/Model.Export", [
                'format' => $format,
                'parameters' => $paramString
            ]);

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch report data. Status: {$response->status()}"
            );
        }

        return $response->json() ?? [];
    }

    /**
     * @throws ConnectionException
     */
    public function getReportParameters(string $id): array
    {
        $response = $this->_query->get(Str::rtrim($this->_serverAPIUrl, '/') . "/Reports($id)/ParameterDefinitions");
        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch report parameters. Status: {$response->status()}"
            );
        }
        return $response->json()['value'] ?? [];
    }

    /**
     * @throws ConnectionException
     * @throws ErroredException
     */
    public function getReportParametersValidated(string $id, array $requestParameters): Collection
    {
        $finalParameters = collect();
        $parameters = $this->getReportParameters($id);

        foreach ($parameters as $parameter) {
            if (isset($requestParameters[$parameter['Name']])) {
                $value = $requestParameters[$parameter['Name']];
                if ($parameter['ParameterType'] === 'DateTime') {

                    if (strtotime($value)) {
                        $finalParameters->put($parameter['Name'], $value);
                        continue;
                    }
                    throw new ErroredException("Parameter {$parameter['Name']} must be a valid date.");
                }

                if ($parameter['ParameterType'] === 'Boolean') {
                    if (in_array(strtolower($value), ['true', 'false', '1', '0'], true)) {
                        $finalParameters->put($parameter['Name'], in_array(strtolower($value), ['true', '1'], true) ? 'true' : 'false');
                        continue;
                    }
                    throw new ErroredException("Parameter {$parameter['Name']} must be a valid boolean value.");
                }

                if ($parameter['ParameterType'] === 'String') {
                    if (!$parameter['ValidValuesIsNull'] && count($parameter['ValidValues']) > 0) {
                        $validValues = collect($parameter['ValidValues'])->pluck('Value')->toArray();

                        if (is_array($value)) {
                            foreach ($value as $singleValue) {
                                if (!in_array($singleValue, $validValues, true)) {
                                    throw new ErroredException("Parameter {$parameter['Name']} must contain only allowed values.");
                                }
                            }
                        } elseif (!in_array($value, $validValues, true)) {
                            throw new ErroredException("Parameter {$parameter['Name']} must be one of the allowed values.");
                        }

                        $finalParameters->put($parameter['Name'], $value);
                        continue;
                    }

                    if (is_string($value) && $value !== '') {
                        $finalParameters->put($parameter['Name'], $value);
                        continue;
                    }
                    throw new ErroredException("Parameter {$parameter['Name']} must be available.");
                }

                if ($parameter['ParameterType'] === 'Integer') {
                    if (!is_numeric($value) || !ctype_digit((string)$value)) {
                        throw new ErroredException("Parameter {$parameter['Name']} must be a valid integer.");
                    }
                    $finalParameters->put($parameter['Name'], (int)$value);
                    continue;
                }

                if ($parameter['ParameterType'] === 'Float') {
                    if (!is_numeric($value)) {
                        throw new ErroredException("Parameter {$parameter['Name']} must be a valid number.");
                    }
                    $finalParameters->put($parameter['Name'], (float)$value);
                    continue;
                }

                // todo Add more parameter type validations here as needed


            } elseif (!$parameter['Nullable'] && !$parameter['AllowBlank']) {
                throw new ErroredException("Parameter {$parameter['Name']} is required.");
            }
        }

        return $finalParameters;
    }

    /**
     * Execute report and get data
     *
     * @param string $path Report path
     * @param array $parameters Report parameters
     * @return array
     * @throws ConnectionException|ErroredException
     */
    public function executeReport(string $path, array $parameters = []): array
    {
        // First, check if the report exists
        $this->getReportByPath($path);

        // Build parameters payload
        $payload = [];
        foreach ($parameters as $key => $value) {
            $payload['parameters'][] = [
                'Name' => $key,
                'Value' => $value
            ];
        }

        $response = $this->_query
            ->post($this->_serverAPIUrl . "Reports(Path='$path')/Model.Execute", $payload);

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to execute report. Status: {$response->status()}"
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Get report details by path
     *
     * @param string $path
     * @return array
     * @throws ErroredException
     */
    public function getReportByPath(string $path): array
    {
        try {
            $response = $this->_query->get($this->_serverAPIUrl . "/Reports(Path='{$path}')");
        } catch (ConnectionException) {
            throw new ErroredException("Could not reach to SSRS Server. Please check your connection.");
        }

        if (!$response->successful()) {
            if ($response->notFound()) {
                throw new ErroredException("Report not found");
            }
            if ($response->serverError()) {
                throw new ErroredException("SSRS Server, Encountered an error");
            }
            if ($response->unauthorized()) {
                throw new ErroredException("System Credentials are not valid");
            }

            throw new ErroredException("Unknown Error: " . $response->status() . ", Contact System Administrator");
        }

        return array_merge($response->json(), ['Route' => $this->_getRoute($path)]);
    }

    private function _getRoute(string $path): string
    {//reports/report/BRERP/Admin/Permissions?rs:embed=true
        return $this->serverURL . "reports/report/" . Str::of($path)->trim()->ltrim('/')->rtrim('/') . '?rs:embed=true';
    }

    /**
     * Download report as PDF
     *
     * @param string $path Report path (e.g., "/BRERP/Admin/Users")
     * @param array $parameters Optional parameters for the report
     * @return StreamedResponse
     * @throws ConnectionException
     */
    public function downloadPdf(string $path, array $parameters = []): StreamedResponse
    {
        // Build parameters string if any parameters are provided
        $paramString = '';
        if (!empty($parameters)) {
            $params = [];
            foreach ($parameters as $key => $value) {
                $params[] = "$key=$value";
            }
            $paramString = '&' . implode('&', $params);
        }

        $response = $this->_query
            ->get($this->_serverAPIUrl . "Reports(Path='$path')/Export?format=PDF{$paramString}");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to download PDF report. Status: {$response->status()}"
            );
        }

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, basename($path) . '.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment',
        ]);
    }

    /**
     * Fetch all reports from SSRS
     *
     * @return Collection
     * @throws ConnectionException
     */
    public function fetch(): Collection
    {
        $response = $this->_query->get($this->_serverAPIUrl . 'Me');

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch SSRS reports. Status: {$response->status()}"
            );
        }

        //dd($response->json());
        return collect($response->json()['value'] ?? []);
    }

    /**
     * Get report details with specific properties
     *
     * @param array $properties Properties to select (e.g., ['Name', 'Path', 'Id'])
     * @return Collection
     * @throws ConnectionException
     */
    public function getReportsWithProperties(array $properties): Collection
    {
        $select = implode(',', $properties);
        $response = $this->_query->get($this->_serverAPIUrl . "Reports?\$select={$select}");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch SSRS reports. Status: {$response->status()}"
            );
        }

        return collect($response->json()['value'] ?? []);
    }

    public function getUsername()
    {
        return $this->_username;
    }
}
