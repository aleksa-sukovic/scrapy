<?php

namespace Scrapy\Parsers;

use Closure;
use Scrapy\Crawlers\Crawly;
use Scrapy\Traits\HandleCallable;

class FunctionParser extends Parser
{
    use HandleCallable;

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
        if ($this->isFunction($this->callback)) {
            return $this->callFunction($this->callback, $crawler, $output);
        }
        return $output;
    }
}
