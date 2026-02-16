<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Core\Report;
use App\Services\ThirdParty\SSRSService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSRSProxyController extends Controller
{
    public function fetchReport(Request $request)
    {
        $ssrsUrl = "http://172.16.2.13:7092/ReportServer?/BRERP/Inventory/ItemCatalogue&rs:embed=true";

        $client = new Client([
            'auth' => ['CraftSilicon\\Mureithi.Maina', (new SSRSService())->getPassword(), 'ntlm'], // NTLM authentication
            'verify' => false, // Disable SSL verification if needed
        ]);

        $response = $client->get($ssrsUrl);

        return response($response->getBody(), $response->getStatusCode(), [
            'Content-Type' => $response->getHeader('Content-Type')[0],
        ]);
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Report $report)
    {
        session()?->forget(['ssrs_cookies', 'ssrs_report_url', 'ssrs_report_path']);

        $service = new SSRSService();

        try {
            $metadata = $service->getReportByPath($report->Path);
            if ($metadata['Type'] !== "Report") {
                throw new ErroredException('invalid report type.');
            }
        } catch (ErroredException $e) {
            return redirect()->back()->with('fail', $e->getMessage());
        }

        // Credentials
        /* $username = $service->getUsername();
         $password = $service->getPassword();*/

        // Make the request with basic auth
        try {
            $response = $service->getQuery()->get($metadata['Route']);
        } catch (ConnectionException $e) {
            Log::error($e);
            abort(500, 'Connection error');
        }

        $body = $response->body();
        $body = preg_replace(
            '/<script src="assets\/([^"]+)" type="module"><\/script>/',
            '<script src="' . route('auth.ssrs.proxy.assets') . '/$1" type="module"></script>',
            $body
        );

        session()?->put('ssrs_report_url', $metadata['Route']);
        session()?->put('ssrs_report_path', $report->Path);
        //session(['ssrs_cookies' => $cookieJar]);


        // Return response with proper headers
        return response($body, $response->status())
            ->withHeaders([
                'Content-Type' => $response->header('Content-Type') ?? 'text/html',
            ]);
    }

    public function assets(Request $request, string $asset = null)
    {
        if (is_null($asset)) {
            return response('No asset specified', 400);
        }

        if (str_starts_with($asset, 'assets')) {
            $path = '/reports/' . $asset;
        } elseif (! str_contains($asset, '/')) {
            $path = '/reports/assets/' . $asset;
        } else {
            $path = '/reports/' . $asset;
        }

        // Get query parameters from request
        $queryString = $request->getQueryString();
        if ($queryString) {
            $path .= '?' . $queryString;
        }

        $service = new SSRSService();
        /* $username = $service->getUsername();
         $password = $service->getPassword();*/

        $assetUrl = rtrim($service->serverURL, '/') . $path;


        try {
            $response = $service->getQuery()->{strtolower($request->method())}($assetUrl);
        } catch (ConnectionException $e) {
            return response('Failed to load asset: ' . $e->getMessage(), 500);
        }

        if (Str::contains($assetUrl, 'Properties')) {
            $contentType = (empty($response->header('Content-Type')))
                ? 'application/json'
                : $response->header('Content-Type');
        } else {
            $contentType = (empty($response->header('Content-Type')))
                ? $this->getContentTypeForExtension(pathinfo($asset, PATHINFO_EXTENSION))
                : $response->header('Content-Type');
        }


        // Return the asset with the appropriate content type
        return response($response->body(), $response->status())
            ->withHeaders([
                'Content-Type' => $contentType,
            ]);
    }

    /**
     * Get the appropriate content type for a file extension
     */
    private function getContentTypeForExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            default => 'application/octet-stream',
        };
    }

    public function report(Request $request, string $path)
    {
        if (Str::contains($request->server('HTTP_COOKIE'), 'AIConnectionString')) {
        }
        $service = new SSRSService();

        try {
            $metadata = $service->getReportByPath('/' . $path);
            if ($metadata['Type'] !== "Report") {
                throw new ErroredException('invalid report type.');
            }
        } catch (ErroredException $e) {
            return redirect()->back()->with('fail', $e->getMessage());
        }

        $completePath = $metadata['Route'];
        $queryString = $request->getQueryString();
        if ($queryString) {
            $completePath .= '?' . $queryString;
        }

        // Credentials



        // Make the request with basic auth
        try {
            $response = $service->getQuery(true)->{strtolower($request->method())}($completePath);
        } catch (ConnectionException $e) {
            Log::error($e);
            abort(500, 'Connection error');
        }

        if ($response->hasHeader('ControlID')) {
        }


        $body = $response->body();
        $body = preg_replace(
            '/<script src="assets\/([^"]+)" type="module"><\/script>/',
            '<script src="' . route('auth.ssrs.proxy.assets') . '/$1" type="module"></script>',
            $body
        );





        // Return response with proper headers
        return response($body, $response->status())
            ->withHeaders(array_merge($response->headers(), [
                'Content-Security-Policy' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092 data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092; frame-src 'self' http://172.16.2.13:7092;",
                'X-Content-Security-Policy' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092 data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092; frame-src 'self' http://172.16.2.13:7092;",

            ]));
    }

    public function preview(Request $request, string $any = null)
    {
        $service = new SSRSService();
        $completePath = rtrim($service->serverURL, '/') . $request->getRequestUri();

        try {
            $response = $service->getQuery()->{strtolower($request->method())}($completePath);
        } catch (ConnectionException $e) {
            return response('Failed to load resource: ' . $e->getMessage(), 500);
        }

        // Special handling for AJAX requests, especially SessionKeepAlive
        if ($request->ajax() || Str::contains($request->getRequestUri(), 'Reserved.ReportViewerWebControl.axd')) {
            // Return the response as-is without modification for AJAX/control requests
            return response($response->body(), $response->status())
                ->withHeaders($response->headers());
        }

        $body = $response->body();
        $body = preg_replace(
            '/<script src="assets\/([^"]+)" type="module"><\/script>/',
            '<script src="' . route('auth.ssrs.proxy.assets') . '/$1" type="module"></script>',
            $body
        );

        // Return response with proper headers
        $proxyResponse = response($body, $response->status())
            ->withHeaders($response->headers());

        foreach ($response->cookies() as $cookie) {
            $proxyResponse->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires() ? (int)(($cookie->getExpires() - time()) / 60) : null,
                $cookie->getPath(),
                config('app.url'), // $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }

        return $proxyResponse;
    }

    public function handleAxd(Request $request)
    {
        $service = new SSRSService();
        $targetUrl = rtrim($service->serverURL, '/') . '/ReportServer/Reserved.ReportViewerWebControl.axd';

        if ($request->getQueryString()) {
            $targetUrl .= '?' . $request->getQueryString();
        }


        try {
            $method = strtolower($request->method());
            $jar = new CookieJar();
            $cookiesValues = '';
            if ($method === 'post') {
                $cookies = collect(explode(';', $request->server('HTTP_COOKIE')))->map(function ($item) {
                    $item = explode('=', $item);
                    $key = Str::trim($item[0]);

                    if (! Str::startsWith($key, 'ai_')) {
                        return null;
                    }

                    return [
                        'key' => $key,
                        'value' => Str::trim($item[1]),
                    ];
                })->filter()->toArray();

                foreach ($cookies as $cookie) {
                    $jar->setCookie(new SetCookie([
                        'Name' => $cookie['key'],
                        'Value' => $cookie['value'],
                        'Domain' => parse_url($service->serverURL, PHP_URL_HOST),
                    ]));
                    $cookiesValues .= $cookie['key'] . '=' . $cookie['value'] . ';';
                }
            }

            $headers = [
                'Accept' => $request->header('Accept', '*/*'),
                'Accept-Language' => $request->header('Accept-Language', 'en-US,en;q=0.9'),
                'User-Agent' => $request->header('User-Agent'),
                'Content-Type' => $request->header('Content-Type', 'application/x-www-form-urlencoded'),
            ];
            if (! empty($cookiesValues)) {
                $headers['Cookie'] = $cookiesValues;
            }

            // Remove null or empty values


            $options = [
                'auth' => [$service->getUsername(), $service->getPassword(), 'ntlm'],
                'verify' => false,
                'headers' => $headers,
                'cookies' => $jar,
            ];


            // Add form data for POST requests
            if ($method === 'post' && $request->post()) {
                $params = collect();
                foreach ($request->post() as $key => $value) {
                    $value = $value ?? '';
                    $params->put($key, $value);
                }
                $options['form_params'] = $params->toArray();
            }

            $client = new Client($options);

            // Forward the request with the same method and parameters
            try {
                $ssrsResponse = ($method === 'get')
                    ? $client->get($targetUrl)
                    : $client->post($targetUrl, $options);
            } catch (Exception | GuzzleException $e) {
                Log::error($e);

                return response('Proxy Error', 500);
            }


            if ($request->isMethod('POST')) {
            }
            if ($request->query('OpType') === 'SessionKeepAlive') {
                // Return a plain text 'OK' response which is what the client expects
                return response('OK', 200)
                    ->header('Content-Type', 'text/plain');
            }


            // Return the response directly without modification
            return response($ssrsResponse->getBody(), $ssrsResponse->getStatusCode())
                ->withHeaders($ssrsResponse->getHeaders());
        } catch (Exception $e) {
            Log::error('SSRS Proxy Error: ' . $e->getMessage() . ' URL: ' . $targetUrl);

            return response('Error proxying to SSRS server: ' . $e->getMessage());
        }
    }
}
