<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Settings\APICredential;
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

        if (!property_exists($ssrsConfig, 'password') || !property_exists($ssrsConfig, 'username') || !property_exists($ssrsConfig, 'host')) {
            throw new ErroredException('invalid report service configuration');
        }

        try {
            $password = Crypt::decryptString($ssrsConfig->password);
        } catch (DecryptException) {
            throw new ErroredException('invalid report service configuration');
        }
        $username = $ssrsConfig->username;
        $this->_username = $username;
        $this->_password = (string)$password;
        $this->serverURL = $ssrsConfig->host;
        $this->_serverAPIUrl = $this->serverURL . "/reports/api/v2.0/";

        $this->_query = Http::withCookies(request()->cookie(), parse_url($this->serverURL, PHP_URL_HOST))
            ->withHeaders(request()->header())->retry(3, 100)->timeout(60)
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

    public static function testConfig(string $Host, string $username, #[SensitiveParameter] string $password): ?string
    {
        try {
            $query = Http::withBasicAuth($username, $password)->withOptions(['auth' => [$username, $password, 'ntlm']])
                ->get(Str::of($Host)->trim()->rtrim('/') . "/reports/api/v2.0/ME");
        } catch (ConnectionException|Exception) {
            return null;
        }
        if ($query->successful() && array_key_exists('DisplayName', $query->json())) {
            return $query->json()['DisplayName'];
        }
        return null;
    }

    /**
     * Export report in specified format
     *
     * @param string $path Report path
     * @param array $parameters Report parameters
     * @param string $format Output format (PDF, EXCEL, WORD, HTML4.0, etc.)
     * @param string $deviceInfo Optional device info XML
     * @return StreamedResponse
     * @throws ConnectionException
     */
    public function exportReport(string $path, string $format = 'JSON', string $deviceInfo = '')
    {
        #https://<YourServer>/ReportServer?/Finance/SalesReport&rs:Format=PDF

        $response = $this->_query
            ->get($this->serverURL . "/ReportServer?" . $path . "&rs:Format=$format");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to export report. Status: {$response->status()}"
            );
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
            'EXCEL' => '.xls',
            'WORD' => '.doc',
            'HTML4.0' => '.html',
            'XML' => '.xml',
            'CSV' => '.csv',
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
     * Get available parameters for a report
     *
     * @param string $path Report path
     * @return array
     * @throws ConnectionException
     */
    public function getReportParameters(string $path): array
    {
        $response = $this->_query
            ->get($this->_serverAPIUrl . "Reports(Path='$path')/Parameters");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch report parameters. Status: {$response->status()}"
            );
        }

        return $response->json()['value'] ?? [];
    }


    /**
     * Execute report and get data
     *
     * @param string $path Report path
     * @param array $parameters Report parameters
     * @return array
     * @throws ConnectionException
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

        dd($response->json());
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
}
