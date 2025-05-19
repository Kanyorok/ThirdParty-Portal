<?php

namespace App\Services\ThirdParty;

use Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSRSService
{
    protected PendingRequest $_query;

    protected string $_reportServerUrl;

    public function __construct()
    {
        $this->_reportServerUrl = "http://172.16.2.13:7092/reports/api/v2.0/";
        $username = "Mureithi.Maina";
        $password = "!1920@GrandMA%";

        $this->_query = Http::withBasicAuth($username, $password)->withOptions(['auth' => [$username, $password, 'ntlm']]);

    }

    public function exportReportWithCustomSettings(string $path, array $parameters = []): StreamedResponse
    {
        $deviceInfo = "<DeviceInfo>
            <OutputFormat>PDF</OutputFormat>
            <PageWidth>8.5in</PageWidth>
            <PageHeight>11in</PageHeight>
            <MarginTop>0.25in</MarginTop>
            <MarginLeft>0.25in</MarginLeft>
            <MarginRight>0.25in</MarginRight>
            <MarginBottom>0.25in</MarginBottom>
        </DeviceInfo>";

        return $this->exportReport($path, $parameters, 'PDF', $deviceInfo);
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
            ->get("http://172.16.2.13:7092/ReportServer?" . $path . "&rs:Format=$format");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to export report. Status: {$response->status()}"
            );
        }

        // Determine content type based on format
        $contentType = $this->getContentType($format);
        $extension = $this->getFileExtension($format);

        dd($response->body());
        /*
        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, basename($path) . $extension, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment',
        ]);*/
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
            ->get($this->_reportServerUrl . "Reports(Path='$path')/Model.Export", [
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
            ->get($this->_reportServerUrl . "Reports(Path='$path')/Parameters");

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
            ->post($this->_reportServerUrl . "Reports(Path='$path')/Model.Execute", $payload);

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
     * @throws ConnectionException
     */
    public function getReportByPath(string $path): array
    {
        $response = $this->_query->get($this->_reportServerUrl . "Reports(Path='{$path}')");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch report details. Status: {$response->status()}"
            );
        }

        return $response->json();
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
            ->get($this->_reportServerUrl . "Reports(Path='$path')/Export?format=PDF{$paramString}");

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
        $response = $this->_query->get($this->_reportServerUrl . 'Me');

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
        $response = $this->_query->get($this->_reportServerUrl . "Reports?\$select={$select}");

        if (!$response->successful()) {
            throw new ConnectionException(
                "Failed to fetch SSRS reports. Status: {$response->status()}"
            );
        }

        return collect($response->json()['value'] ?? []);
    }
}
