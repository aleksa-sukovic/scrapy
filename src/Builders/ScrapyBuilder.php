<?php

namespace Scrapy\Builders;

use Scrapy\Scrapy;
use Scrapy\Readers\IReader;
use Scrapy\Parsers\IParser;
use Scrapy\Readers\UrlReader;
use Scrapy\Traits\HandleCallable;
use Scrapy\Parsers\FunctionParser;

/**
 * Class ScrapyBuilder.
 *
 * Builder class responsible for making different versions of Scrapy.
 *
 * @package Scrapy\Builders
 */
class ScrapyBuilder
{
    use HandleCallable;

    /**
     * Static builder initializer method.
     *
     * Serves as alternative constructor allowing for fluent method chaining.
     *
     * @return ScrapyBuilder
     */
    public static function make(): ScrapyBuilder
    {
        return new ScrapyBuilder();
    }

    /** @var Scrapy */
    protected $scrapy;

    /**
     * ScrapyBuilder constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Adds params to Scrapy.
     *
     * @param array $params Associative array of params.
     * @return ScrapyBuilder
     */
    public function params(array $params): ScrapyBuilder
    {
        $this->scrapy->setParams($params);
        return $this;
    }

    /**
     * Sets url as read source.
     *
     * @param string $url Url to be read.
     * @return ScrapyBuilder
     */
    public function url(string $url): ScrapyBuilder
    {
        $this->scrapy->setReader(new UrlReader($url));
        return $this;
    }

    /**
     * Adds parser to current Scrapy instance.
     *
     * @param $parser callable|string|IParser Callable representing parser, concrete IParser implementation or IParser implementation class name
     * @return ScrapyBuilder
     */
    public function parser($parser): ScrapyBuilder
    {
        if (is_string($parser))   $parser = new $parser;
        if (is_callable($parser)) $parser = new FunctionParser($parser);

        $this->scrapy->addParser($parser);

        return $this;
    }

    /**
     * Adds parsers to current Scrapy instance.
     *
     * @param array<callable|string|IParser> $parsers Array of parsers.
     * @return ScrapyBuilder
     */
    public function parsers(array $parsers): ScrapyBuilder
    {
        foreach ($parsers as $parser) {
            $this->parser($parser);
        }
        return $this;
    }

    /**
     * Sets the reader to be used by current Scrapy instance.
     *
     * @param IReader $reader Concrete implementation of IReader interface.
     * @return ScrapyBuilder
     */
    public function reader(IReader $reader): ScrapyBuilder
    {
        $this->scrapy->setReader($reader);
        return $this;
    }

    /**
     * Functions that checks for html validity.
     *
     * Sometime you want to check if given html is valid even if it was red successfully from source.
     * By specifying this callback, which gets Crawly instance as its first argument, you
     * can return true or false indicating if provided html is valid by your criteria.
     *
     * @param $callback callable (Crawly $crawly):bool Callback determining if given html input is valid.
     * @return ScrapyBuilder
     */
    public function htmlChecker($callback): ScrapyBuilder
    {
        $this->scrapy->setHtmlChecker($callback);
        return $this;
    }

    /**
     * Creates the new Scrapy instance, discarding the old one.
     *
     * @return ScrapyBuilder
     */
    public function reset(): ScrapyBuilder
    {
        $this->scrapy = new Scrapy();
        return $this;
    }

    /**
     * Returns the configured Scrapy instance.
     *
     * @return Scrapy
     */
    public function build(): Scrapy
    {
        return $this->scrapy;
    }
}
