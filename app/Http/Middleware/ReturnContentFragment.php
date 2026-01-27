<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReturnContentFragment
{
    /**
     * Handle an incoming request.
     * If AJAX, return only the content section of Blade-rendered views.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only act on AJAX/XHR requests that explicitly request a partial fragment
        // (we use a custom header X-Partial to avoid interfering with other AJAX endpoints)
        if (! $request->ajax() || ! $request->headers->has('X-Partial')) {
            return $response;
        }

        // If controller returned a Laravel View instance, render its sections
        if (is_object($response) && method_exists($response, 'renderSections')) {
            try {
                $sections = $response->renderSections();
                $payload = $sections['content'] ?? $response->render();
                // If the view rendered a scripts section (e.g. @push('scripts')), include it
                if (! empty($sections['scripts'])) {
                    $payload .= $sections['scripts'];
                }

                return response($payload);
            } catch (\Throwable $e) {
                // fall through to default handling
            }
        }

        // If response is a Symfony Response with HTML, attempt to extract the mainBodyContent element
        if ($response instanceof Response) {
            $content = $response->getContent();
            if (is_string($content)) {
                // Try a simple DOM parse to extract #mainBodyContent
                libxml_use_internal_errors(true);
                $doc = new \DOMDocument();
                if (@$doc->loadHTML($content)) {
                    $node = $doc->getElementById('mainBodyContent');
                    if ($node) {
                        $inner = '';
                        foreach ($node->childNodes as $child) {
                            $inner .= $doc->saveHTML($child);
                        }

                        return response($inner, $response->getStatusCode(), $response->headers->all());
                    }
                }
            }
        }

        return $response;
    }
}
