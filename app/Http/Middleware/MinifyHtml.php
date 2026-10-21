<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $this->shouldMinify() &&
            $this->isHtml($response)
        ) {
            $response->setContent($this->minify($response->getContent()));
        }

        return $response;
    }

    /**
     * Determine if the response should be minified.
     */
    protected function shouldMinify(): bool
    {
        // return true;
        return app()->isProduction() &&
            !config('app.debug') &&
            !config('debugbar.enabled');
    }

    /**
     * Check if the response is HTML.
     */
    protected function isHtml(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        return str_contains(strtolower($contentType), 'text/html');
    }

    /**
     * Minify the HTML content.
     */
    protected function minify(string $html): string
    {
        // Preserve script, style, pre, and textarea tags
        $scripts = [];
        $styles = [];
        $pres = [];
        $textareas = [];

        // Extract and preserve script tags
        $html = preg_replace_callback(
            '/<script\b[^>]*>(.*?)<\/script>/is',
            function ($matches) use (&$scripts) {
                $placeholder = '___SCRIPT_' . count($scripts) . '___';
                $scripts[] = $matches[0];
                return $placeholder;
            },
            $html
        );

        // Extract and preserve style tags
        $html = preg_replace_callback(
            '/<style\b[^>]*>(.*?)<\/style>/is',
            function ($matches) use (&$styles) {
                $placeholder = '___STYLE_' . count($styles) . '___';
                $styles[] = $matches[0];
                return $placeholder;
            },
            $html
        );

        // Extract and preserve pre tags (code blocks, formatted text)
        $html = preg_replace_callback(
            '/<pre\b[^>]*>(.*?)<\/pre>/is',
            function ($matches) use (&$pres) {
                $placeholder = '___PRE_' . count($pres) . '___';
                $pres[] = $matches[0];
                return $placeholder;
            },
            $html
        );

        // Extract and preserve textarea tags
        $html = preg_replace_callback(
            '/<textarea\b[^>]*>(.*?)<\/textarea>/is',
            function ($matches) use (&$textareas) {
                $placeholder = '___TEXTAREA_' . count($textareas) . '___';
                $textareas[] = $matches[0];
                return $placeholder;
            },
            $html
        );

        // Minify HTML (excluding scripts, styles, pre blocks, and textareas)
        $search = [
            '/\>[^\S ]+/s',                    // Strip whitespaces after tags, except space
            '/[^\S ]+\</s',                    // Strip whitespaces before tags, except space
            '/(\s)+/s',                        // Shorten multiple whitespace sequences
            '/<!--(?!\[if\s)(?!<!)[^\[>].*?-->/s'  // Remove HTML comments except IE conditional comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        $html = preg_replace($search, $replace, $html);

        // Restore pre tags
        foreach ($pres as $index => $pre) {
            $html = str_replace('___PRE_' . $index . '___', $pre, $html);
        }

        // Restore textarea tags
        foreach ($textareas as $index => $textarea) {
            $html = str_replace('___TEXTAREA_' . $index . '___', $textarea, $html);
        }

        // Restore script tags
        foreach ($scripts as $index => $script) {
            $html = str_replace('___SCRIPT_' . $index . '___', $script, $html);
        }

        // Restore style tags
        foreach ($styles as $index => $style) {
            $html = str_replace('___STYLE_' . $index . '___', $style, $html);
        }

        return $html;
    }
}
