<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Core\Report;
use App\Services\ThirdParty\SSRSService;
use GuzzleHttp\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
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
            'Content-Type' => $response->getHeader('Content-Type')[0]
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

        //  dd($metadata);
        // Credentials
        /* $username = $service->getUsername();
         $password = $service->getPassword();*/

        // $cookieJar = new CookieJar();
        // Make the request with basic auth
        try {
            /* Http::globalOptions([
                'allow_redirects' => false,
                ])->withOptions([
                'auth' => [$username, $password, 'ntlm'],
                'cookies' => $cookieJar,
            ])->withHeaders([
                'User-Agent' => $request->userAgent(),
            ])*/

            $response = $service->getQuery()->get($metadata['Route']);
        } catch (ConnectionException $e) {
            dd($e);//todo show view error
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
            //$asset = str_replace('assets/', '', $asset);
            $path = '/reports/' . $asset;
        } else if (!str_contains($asset, '/')) {
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
            /*$response = Http::withOptions([
                'auth' => [$username, $password, 'ntlm'],
            ])->withHeaders([
                    'Accept-Language'=>'en-GB',
                    'User-Agent' => $request->userAgent(),
                    'Referer' => session('ssrs_report_url'),
            ])*/
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
        /*  $username = $service->getUsername();
          $password = $service->getPassword();*/


        // Make the request with basic auth
        try {
            /*$response = Http::globalOptions([
                'allow_redirects' => false,
            ])->withOptions([
                'auth' => [$username, $password, 'ntlm'],
            ])->withHeaders([
                'User-Agent' => $request->userAgent(),
            ])*/
            $response = $service->getQuery(true)->{strtolower($request->method())}($completePath);
        } catch (ConnectionException $e) {
            dd($e);//todo show view error
        }

        if ($response->hasHeader('ControlID')) {
            dd($response->headers(), 'Headers');
        }


        $body = $response->body();
        $body = preg_replace(
            '/<script src="assets\/([^"]+)" type="module"><\/script>/',
            '<script src="' . route('auth.ssrs.proxy.assets') . '/$1" type="module"></script>',
            $body
        );

        /*    session()?->put('ssrs_report_url', $metadata['Route']);
            session()?->put('ssrs_report_path', $path);
            session(['ssrs_cookies' => $cookieJar]);*/

        //  dd($response);


        // Return response with proper headers
        return response($body, $response->status())
            ->withHeaders(array_merge($response->headers(), [
                'Content-Security-Policy' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092 data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092; frame-src 'self' http://172.16.2.13:7092;",
                'X-Content-Security-Policy' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092 data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://172.16.2.13:7092; frame-src 'self' http://172.16.2.13:7092;"

            ]));
    }

    public function preview(Request $request, string $any = null)
    {
        $service = new SSRSService();

        //$completePath =rtrim($service->serverURL, '/').'/ReportServer/'.$any;
        $completePath = rtrim($service->serverURL, '/') . $request->getRequestUri();
        //Str::of($request->url())->replace(config('app.url'), rtrim($service->serverURL, '/'));

        /* if ($request->getQueryString()) {
             $completePath .= '?' . $request->getQueryString();
         }

         if (Str::contains($any, 'Pages/ReportViewer.aspx')) {
             $completePath = Str::replace('=&rc','&rc', $completePath);
         }*/
        // Credentials
        /*$username = $service->getUsername();
        $password = $service->getPassword();*/


        // Make the request with basic auth
        try {
            /*$response = Http::->withOptions([
                'auth' => [$username, $password, 'ntlm'],
            ])->withHeaders([
                'User-Agent' => $request->userAgent(),
            ])*/

            $response = $service->getQuery()->{strtolower($request->method())}($completePath);
        } catch (ConnectionException $e) {
            dd($completePath, $e);//todo show view error
        }


        if ($response->hasHeader('ControlID')) {
            dd($response->headers(), 'Headers');
        }


        if (!$response->successful()) {
            /* if ($response->body() ==="OK"){
                 $status = 200;
             }else{*/
            dd("Failed", $response);//todo show view error
            //  }

        }


        $body = $response->body();
        $body = preg_replace(
            '/<script src="assets\/([^"]+)" type="module"><\/script>/',
            '<script src="' . route('auth.ssrs.proxy.assets') . '/$1" type="module"></script>',
            $body
        );

        //  $body = str_replace('<script>', '<script nonce="reportscript">', $body);


        /*    session()?->put('ssrs_report_url', $metadata['Route']);
            session()?->put('ssrs_report_path', $path);
            session(['ssrs_cookies' => $cookieJar]);*/

        //  dd($response);


        // Return response with proper headers
        $proxyResponse = response($body, $response->status())
            ->withHeaders($response->headers());
        /*array_merge($response->headers(), [
            'Content-Security-Policy' => "script-src 'self' 'nonce-reportscript' 'unsafe-inline' 'unsafe-eval';"
        ])*/
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

}
