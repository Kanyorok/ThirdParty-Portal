<?php

namespace App\Spiders;

use App\Helpers\StringHelper;
use Generator;
use Illuminate\Support\Str;
use RoachPHP\Downloader\Middleware\ExecuteJavascriptMiddleware;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class CompetitorSpider extends BasicSpider
{
    public array $downloaderMiddleware = [
                                          RequestDeduplicationMiddleware::class,
                                          [
                                           UserAgentMiddleware::class,
                                           ['userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36'],
                                          ],
                                          [
                                           ExecuteJavascriptMiddleware::class,
                                           [],
                                          ],
                                         ];

    public array $spiderMiddleware = [

                                     ];

    public array $itemProcessors = [

                                   ];

    public array $extensions = [
                                StatsCollectorExtension::class,
                               ];

    public int $concurrency = 2;

    public int $requestDelay = 1;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $domain = StringHelper::getDomain($this->configuration->startUrls[0]);
        if ($response->getStatus() >= 200 && $response->getStatus() < 300) {
            $logo = $this->_setLogo($response);
            if (is_string($logo)) {
                yield $this->item(['logo' => $logo]);
            }

            $data = collect();
            foreach ($response->filter('a')->links() as $link) {
                if (! Str::contains($link->getUri(), [$domain, 'www.' . $domain]) || ($data->contains($link->getUri()) || ! Str::contains($link->getUri(), ['product', 'loan', 'saving', 'account']))) {
                    continue;
                }
                if (Str::contains($link->getUri(), ['download'])) {
                    continue;
                }
                $data->push($link->getUri());
                yield $this->request('GET', $link->getUri(), 'parseProductsPages');
            }

            yield $this->item(['landing' => StringHelper::removeScripts($response->getBody())]);
        }
    }

    private function _setLogo(Response $response): ?string
    {
        foreach ($response->filter('img')->images() as $image) {
            if (Str::contains($image->getUri(), 'logo', true) && ! Str::contains($image->getUri(), 'widget', true)) {
                return $image->getUri();
            }
        }

        return null;
    }

    public function parseProductsPages(Response $response): Generator
    {
        if ($response->getStatus() >= 200 && $response->getStatus() < 300) {
            yield $this->item(['content' => StringHelper::removeScripts($response->getBody())]);
        }
    }
}
