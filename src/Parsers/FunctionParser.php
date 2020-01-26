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

    public function process(Crawly $crawler, &$output, $params)
    {
        if (is_callable($this->callback)) {
            call_user_func($this->callback, $crawler, $output, $params);
        }
    }
}
