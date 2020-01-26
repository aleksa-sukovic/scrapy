<?php

namespace Scrapy\Builders;

use Scrapy\Scrapy;

class ScrapyBuilder
{
    public static function make(): ScrapyBuilder
    {
        return new ScrapyBuilder();
    }

    /**
     * @var Scrapy
     */
    protected $scrapy;

    public function __construct()
    {
        $this->reset();
    }

    public function withParams(array $params): ScrapyBuilder
    {
        $this->scrapy->setParams($params);
        return $this;
    }

    public function reset(): ScrapyBuilder
    {
        $this->scrapy = new Scrapy();
        return $this;
    }

    public function build(): Scrapy
    {
        return $this->scrapy;
    }
}
