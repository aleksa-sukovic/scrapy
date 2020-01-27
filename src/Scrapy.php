<?php

namespace Scrapy;

use Exception;
use Scrapy\Crawlers\Crawly;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Parsers\IParser;
use Scrapy\Parsers\Parser;
use Scrapy\Reader\Reader;
use Scrapy\Traits\HandleCallable;

class Scrapy
{
    use HandleCallable;

    protected $reader;
    protected $beforeScrapeCallback;
    protected $afterScrapeCallback;
    protected $onParseErrorCallback;
    protected $onFailCallback;
    protected $validityChecker;
    protected $html;
    protected $parsers;
    protected $errors;
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
        $this->onParseErrorCallback = null;
        $this->onFailCallback = null;
        $this->validityChecker = null;
    }

    public function scrape(string $url)
    {
        try {
            $this->reset();

            $this->html = $this->reader->read($url);
            $this->runValidityChecker($this->html);

            $this->html = $this->beforeScrape($this->html);
            $this->runParsers(new Crawly($this->html));
            $this->result = $this->afterScrape($this->result);
        } catch (ScrapeException $e) {
            $this->errors[] = $e->toArray();
        } catch (Exception $e) {
            $this->errors[] = (new ScrapeException($e->getMessage(), $e->getCode()))->toArray();
        } finally {
            if ($this->failed())
                $this->result = $this->callFunction($this->onFailCallback, $this->result) ?? $this->result;

            return $this->result;
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

    private function runParsers(Crawly $crawly): void
    {
        foreach ($this->parsers as $parser) {
            try {
                $this->result = $parser->process($crawly, $this->result, $this->params);
            } catch (Exception $e) {
                $this->handleParserError($parser, $e);
            }
        }
    }

    public function reset(): void
    {
        $this->errors = [];
        $this->result = [];
    }

    protected function beforeScrape(string $html): string
    {
        return $this->callFunction($this->beforeScrapeCallback, $html) ?? $html;
    }

    protected function afterScrape(&$scrapingResult): array
    {
        return $this->callFunction($this->afterScrapeCallback, $scrapingResult) ?? $scrapingResult;
    }

    protected function handleParserError(IParser $parser, Exception $e): void
    {
        $this->callFunction($this->onParseErrorCallback, $parser);

        $this->errors[] = ['object' => $parser, 'message' => $e->getMessage(), 'status_code' => $e->getCode()];
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

    public function failed(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
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

    public function setOnParseErrorCallback($callback): void
    {
        $this->onParseErrorCallback = $callback;
    }

    public function onParseErrorCallback(): callable
    {
        return $this->onParseErrorCallback;
    }

    public function setOnFailCallback($callback): void
    {
        $this->onFailCallback = $callback;
    }

    public function onFailCallback(): callable
    {
        return $this->onFailCallback;
    }
}
