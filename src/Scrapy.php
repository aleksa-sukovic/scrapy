<?php

namespace Scrapy;

use Exception;
use Scrapy\Crawlers\Crawly;
use Scrapy\Parsers\IParser;
use Scrapy\Reader\Reader;
use Scrapy\Traits\HandleCallable;

class Scrapy
{
    use HandleCallable;

    /**
     * @var Reader
     */
    protected $reader;

    protected $beforeScrapeCallback;
    protected $afterScrapeCallback;
    protected $parsers;
    protected $params;
    protected $errors;

    public function __construct()
    {
        $this->parsers = [];
        $this->errors = [];
        $this->params = [];
        $this->reader = new Reader();
        $this->beforeScrapeCallback = null;
        $this->afterScrapeCallback = null;
    }

    public function scrape(string $url)
    {
        $html = $this->reader->read($url);
        $html = $this->beforeScrape($html);
        $crawler = new Crawly($html);
        $result = [];

        foreach ($this->parsers as $parser) {
            try {
                $parser->process($crawler, $result, $this->params);
            } catch (Exception $e) {
                $this->errors[] = ['parser' => get_class($parser), 'message' => $e->getMessage(), 'code' => $e->getCode()];
            }
        }

        return $this->afterScrape($result);
    }

    protected function beforeScrape(string $html): string
    {
        return $this->isFunction($this->beforeScrapeCallback) ?
            $this->callFunction($this->beforeScrapeCallback, $html) : $html;
    }

    protected function afterScrape(&$scrapingResult): array
    {
        return $this->isFunction($this->afterScrapeCallback) ?
            $this->callFunction($this->afterScrapeCallback, $scrapingResult) : $scrapingResult;
    }

    public function setParsers(array $parsers): void
    {
        $this->parsers = $parsers;
    }

    public function addParser(IParser $parser): void
    {
        $this->parsers[] = $parser;
    }

    public function parsers(): array
    {
        return $this->parsers;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function setBeforeScrapeCallback($callback): void
    {
        $this->beforeScrapeCallback = $callback;
    }

    public function beforeScrapeCallback(): ?callable
    {
        return $this->beforeScrapeCallback;
    }

    public function setAfterScrapeCallback($callback): void
    {
        $this->afterScrapeCallback = $callback;
    }

    public function afterScrapeCallback(): ?callable
    {
        return $this->afterScrapeCallback;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
