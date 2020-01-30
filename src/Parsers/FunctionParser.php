<?php

namespace Scrapy\Parsers;

use Closure;
use Scrapy\Crawlers\Crawly;
use Scrapy\Traits\HandleCallable;

/**
 * Class FunctionParser.
 *
 * Parser constructed from a Closure.
 *
 * @package Scrapy\Parsers
 */
class FunctionParser extends Parser
{
    use HandleCallable;

    /** @var callable */
    protected $callback;

    /**
     * FunctionParser constructor.
     *
     * @param $callback Closure Function to be called on parser's "process" call.
     * @param array $params Additional parser's params.
     */
    public function __construct($callback, $params = [])
    {
        parent::__construct($params);

        $this->callback = Closure::bind($callback, $this);
    }

    /**
     * Processes the given html by passing it to a specified closure.
     *
     * @param Crawly $crawler Instance of crawler containing desired html.
     * @param array $output Array representing the current scraping result.
     * @return array Array representing the new scraping result.
     */
    public function process(Crawly $crawler, array $output): array
    {
        if ($this->isFunction($this->callback)) {
            return $this->callFunction($this->callback, $crawler, $output);
        }
        return $output;
    }
}
