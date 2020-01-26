<?php

namespace Scrapy\Builders;

use Scrapy\Parsers\FunctionParser;
use Scrapy\Reader\Reader;
use Scrapy\Scrapy;
use Scrapy\Traits\HandleCallable;

class ScrapyBuilder
{
    use HandleCallable;

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

    public function withParser($parser): ScrapyBuilder
    {
        if (is_string($parser)) $parser = new $parser;
        if (is_callable($parser)) $parser = new FunctionParser($parser);

        $this->scrapy->addParser($parser);

        return $this;
    }

    public function withParsers(array $parsers): ScrapyBuilder
    {
        foreach ($parsers as $parser) {
            $this->withParser($parser);
        }

        return $this;
    }

    public function beforeScrape($callback): ScrapyBuilder
    {
        if ($this->isFunction($callback)) {
            $this->scrapy->setBeforeScrapeCallback($callback);
        }
        return $this;
    }

    public function afterScrape($callback): ScrapyBuilder
    {
        if ($this->isFunction($callback)) {
            $this->scrapy->setAfterScrapeCallback($callback);
        }
        return $this;
    }

    public function reader(Reader $reader): ScrapyBuilder
    {
        $this->scrapy->setReader($reader);
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
