<?php

namespace Scrapy\Parsers;

use Closure;
use Scrapy\Crawlers\Crawly;

class FunctionParser extends Parser
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct($callback, $params = [])
    {
        parent::__construct($params);

        $this->callback = Closure::bind($callback, $this);
    }

    public function process(Crawly $crawler, array $output): array
    {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $crawler, $output);
        }

        return $output;
    }
}
