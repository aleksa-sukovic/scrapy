<?php

namespace Scrapy;

use Error;
use Exception;
use Scrapy\Crawlers\Crawly;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Parsers\Parser;
use Scrapy\Reader\IReader;
use Scrapy\Reader\NullReader;
use Scrapy\Traits\HandleCallable;

class Scrapy
{
    use HandleCallable;

    protected $reader;
    protected $htmlCheckerFunction;
    protected $html;
    protected $parsers;
    protected $params;

    public function __construct()
    {
        $this->parsers = [];
        $this->params = [];
        $this->errors = [];
        $this->html = '';
        $this->reader = new NullReader();
        $this->htmlCheckerFunction = null;
    }

    public function scrape(): array
    {
        try {
            $this->html = $this->reader()->read();
            $this->runValidityChecker($this->html);

            return $this->runParsers(new Crawly($this->html));
        } catch (Exception|Error $e) {
            throw new ScrapeException($e->getMessage(), $e->getCode());
        }
    }

    private function runValidityChecker(string $html): void
    {
        if (!$this->isFunction($this->htmlCheckerFunction)) {
            return;
        }

        if (!$this->callFunction($this->htmlCheckerFunction, new Crawly($html))) {
            throw new ScrapeException('Page html validation failed.', 400);
        }
    }

    private function runParsers(Crawly $crawly): array
    {
        $result = [];
        foreach ($this->parsers as $parser) {
            $crawly->reset();

            $result = $parser->process($crawly, $result, $this->params);
        }
        return $result;
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

    public function reader(): IReader
    {
        return $this->reader;
    }

    public function setReader(IReader $reader): void
    {
        $this->reader = $reader;
    }

    public function setHtmlChecker($function): void
    {
        $this->htmlCheckerFunction = $function;
    }

    public function validityChecker(): callable
    {
        return $this->htmlCheckerFunction;
    }

    public function html(): string
    {
        return $this->html;
    }
}
