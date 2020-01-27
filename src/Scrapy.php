<?php

namespace Scrapy;

use Error;
use Exception;
use Scrapy\Crawlers\Crawly;
use Scrapy\Readers\IReader;
use Scrapy\Parsers\IParser;
use Scrapy\Readers\NullReader;
use Scrapy\Traits\HandleCallable;
use Scrapy\Exceptions\ScrapeException;

/**
 * Class Scrapy.
 *
 * @package Scrapy
 */
class Scrapy
{
    use HandleCallable;

    /** @var IReader */
    protected $reader;

    /** @var callable|null Optional callback function to check HTML validity.  */
    protected $htmlCheckerFunction;

    /** @var string String representation of HTML to be scraped. */
    protected $html;

    /** @var array<IParser> Array of parsers to be run. */
    protected $parsers;

    /** @var array Associative array of params to be passed to each parser. */
    protected $params;

    /**
     * Scrapy constructor.
     */
    public function __construct()
    {
        $this->parsers = [];
        $this->params = [];
        $this->html = '';
        $this->reader = new NullReader();
        $this->htmlCheckerFunction = null;
    }

    /**
     * Runs the read string trough available parsers returning.
     *
     * @return array Scraping result after running trough all parsers.
     * @throws ScrapeException In case html validation or any of the parsers fail.
     */
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

    /**
     * Checks if given HTML string is valid by running it trough callback function.
     *
     * @param string $html HTML string to be checked.
     * @throws ScrapeException In case htmlCheckerFunction exists and returns false indicating that given HTML string is not valid.
     */
    private function runValidityChecker(string $html): void
    {
        if (!$this->isFunction($this->htmlCheckerFunction)) {
            return;
        }

        if (!$this->callFunction($this->htmlCheckerFunction, new Crawly($html))) {
            throw new ScrapeException('Page html validation failed.', 400);
        }
    }

    /**
     * Passes given Crawly crawler trough available parsers.
     *
     * @param Crawly $crawly Instance of crawler made from read HTML string.
     * @return array Concatenated output of all parsers.
     */
    private function runParsers(Crawly $crawly): array
    {
        $result = [];
        foreach ($this->parsers as $parser) {
            $crawly->reset();

            $result = $parser->process($crawly, $result, $this->params);
        }
        return $result;
    }

    /**
     * Adds specified parser to available parsers array.
     *
     * @param IParser $parser Concrete instance of IParser interface.
     */
    public function addParser(IParser $parser): void
    {
        $parser->setParams($this->params);

        $this->parsers[] = $parser;
    }

    /**
     * @return array<IParser> Array of available parsers.
     */
    public function parsers(): array
    {
        return $this->parsers;
    }

    /**
     * @return array Associative array representing additional parser's parameters.
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * @param array $params Associative array representing additional parameters to be passed to each parser.
     */
    public function setParams(array $params): void
    {
        $this->params = $params;

        foreach ($this->parsers as $parser) {
            $parser->setParams($params);
        }
    }

    /**
     * @return IReader Reader associated with this Scrapy instance.
     */
    public function reader(): IReader
    {
        return $this->reader;
    }

    /**
     * @param IReader $reader IReader implementation to be injected into this Scrapy instance.
     */
    public function setReader(IReader $reader): void
    {
        $this->reader = $reader;
    }

    /**
     * @param $function(Crawly $crawly): bool Function to be called for checking validity of HTML string.
     */
    public function setHtmlChecker($function): void
    {
        $this->htmlCheckerFunction = $function;
    }

    /**
     * @return callable Currently set callback function for checking validity of HTML string.
     */
    public function htmlChecker(): ?callable
    {
        return $this->htmlCheckerFunction;
    }

    /**
     * @return string String representing HTML to be scraped.
     */
    public function html(): string
    {
        return $this->html;
    }
}
