<?php

namespace Scrapy;

use Error;
use Exception;
use Scrapy\Crawlers\Crawly;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Parsers\Parser;
use Scrapy\Reader\Reader;
use Scrapy\Traits\HandleCallable;

class Scrapy
{
    use HandleCallable;

    protected $reader;
    protected $beforeScrapeCallback;
    protected $afterScrapeCallback;
    protected $validityChecker;
    protected $html;
    protected $parsers;
    protected $params;
    protected $result;

    public function __construct()
    {
        $this->parsers = [];
        $this->params = [];
        $this->errors = [];
        $this->result = [];
        $this->html = '';
        $this->reader = new Reader();
        $this->beforeScrapeCallback = null;
        $this->afterScrapeCallback = null;
        $this->validityChecker = null;
    }

    public function scrape(string $url)
    {
        try {
            $this->html = $this->reader->read($url);
            $this->runValidityChecker($this->html);

            $this->html = $this->beforeScrape($this->html);
            $this->result = $this->runParsers(new Crawly($this->html));
            $this->result = $this->afterScrape($this->result);
            return $this->result;
        } catch (Exception|Error $e) {
            throw new ScrapeException($e->getMessage(), $e->getCode());
        }
    }

    private function runValidityChecker(string $html): void
    {
        if (!$this->isFunction($this->validityChecker)) {
            return;
        }

        if (!$this->callFunction($this->validityChecker, new Crawly($html))) {
            throw new ScrapeException('Page html validation failed.', 400);
        }
    }

    private function runParsers(Crawly $crawly): array
    {
        $result = [];
        foreach ($this->parsers as $parser) {
            $result = $parser->process($crawly, $result, $this->params);
        }
        return $result;
    }

    protected function beforeScrape(string $html): string
    {
        return $this->callFunction($this->beforeScrapeCallback, $html) ?? $html;
    }

    protected function afterScrape(&$scrapingResult): array
    {
        return $this->callFunction($this->afterScrapeCallback, $scrapingResult) ?? $scrapingResult;
    }

    public function addParser(Parser $parser): void
    {
        $parser->setParams($this->params);

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

        foreach ($this->parsers as $parser) {
            $parser->setParams($params);
        }
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

    public function reader(): Reader
    {
        return $this->reader;
    }

    public function setReader(Reader $reader): void
    {
        $this->reader = $reader;
    }

    public function setValidityCheck($callback): void
    {
        $this->validityChecker = $callback;
    }

    public function validityChecker(): callable
    {
        return $this->validityChecker;
    }

    public function html(): string
    {
        return $this->html;
    }

    public function result(): array
    {
        return $this->result;
    }
}
