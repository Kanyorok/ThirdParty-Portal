<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleHtmxRequests
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if this is an HTMX request
        if ($request->header('HX-Request')) {
            // For HTMX requests, we want to return only the content section
            // This assumes the view extends a layout and has a @section('content')

            if ($response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
                $content = $response->getContent();

                // Extract only the content section if it's wrapped in a layout
                // Look for the main content div
                if (preg_match('/<div[^>]*id="page-content"[^>]*>(.*?)<\/div>/s', $content, $matches)) {
                    $response->setContent($matches[1]);
                } else {
                    // Fallback: try to extract content between specific markers
                    if (preg_match('/<!-- START CONTENT -->(.*?)<!-- END CONTENT -->/s', $content, $matches)) {
                        $response->setContent($matches[1]);
                    }
                }
            }
        }

        return $response;
    }
}
