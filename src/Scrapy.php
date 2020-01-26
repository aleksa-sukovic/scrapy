<?php

namespace Scrapy;

use Exception;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Reader\Reader;
use Scrapy\Traits\HandleCallable;
use Symfony\Component\DomCrawler\Crawler;

class Scrapy
{
    use HandleCallable;

    /**
     * @var string[]
     */
    protected $parsers;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var callable
     */
    protected $beforeScrapeCallback;

    /**
     * @var callable
     */
    protected $afterScrapeCallback;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $errors;

    public function __construct()
    {
        $this->parsers = [];
        $this->errors = [];
        $this->params = [];
        $this->reader = new Reader;
        $this->beforeScrapeCallback = null;
        $this->afterScrapeCallback = null;
    }

    /**
     * @param string $url
     *
     * @return array
     * @throws ScrapeException
     */
    public function scrape(string $url)
    {
        $html = $this->reader->read($url);
        $result = [];

        $crawler = new Crawler($html);
        $crawler = $this->isFunction($this->beforeScrapeCallback) ?
            $this->callFunction($this->beforeScrapeCallback, $crawler) : $crawler;

        foreach ($this->parsers as $parser) {
            try {
                app($parser)->process($crawler, $result, $this->params);
            } catch (Exception $e) {
                $this->errors[] = ['parser' => $parser, 'message' => $e->getMessage(), 'code' => $e->getCode()];
            }
        }

        $result = $this->isFunction($this->afterScrapeCallback) ?
            $this->callFunction($this->afterScrapeCallback, $result) : $result;

        return $result;
    }

    /**
     * @param callable $callback
     *     - Function to be called before running raw html trough parsers.
     * 		 Function receives string representing raw html of page.
     *
     * @return Scrapy
     */
    public function beforeScrape(callable $callback): Scrapy
    {
        $this->beforeScrapeCallback = $callback;

        return $this;
    }

    /**
     * @param callable $callback
     *     - Function to be called after parsers have processed the input.
     *       Processed object is passed as first argument of the function.
     *
     * @return Scrapy
     */
    public function afterScrape(callable $callback): Scrapy
    {
        $this->afterScrapeCallback = $callback;

        return $this;
    }

    /**
     * @param string[] $parsers
     *     - Array of parser's class names.
     *
     * @return Scrapy
     */
    public function withParsers(array $parsers): Scrapy
    {
        $this->parsers = $parsers;

        return $this;
    }

    public function withParams(array $params): Scrapy
    {
        $this->params = $params;

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
