<?php

namespace Scrapy\Parsers;

use Scrapy\Crawlers\Crawly;

class FunctionParser implements IParser
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function process(Crawly $crawler, array $output, array $params): array
    {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $crawler, $output, $params);
        }
        return $output;
    }
}
