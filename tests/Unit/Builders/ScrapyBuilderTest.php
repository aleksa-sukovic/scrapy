<?php

namespace Scrapy\Tests\Unit\Builders;

use PHPUnit\Framework\TestCase;
use Scrapy\Builders\ScrapyBuilder;

class ScrapyBuilderTest extends TestCase
{
    public function test_it_adds_params()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParams(['foo' => 'bar'])
            ->build();

        $this->assertEquals(['foo' => 'bar'], $scrapy->params());
    }

    public function test_rested_method_reverts_changes()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParams(['foo' => 'bar'])
            ->reset()
            ->build();

        $this->assertEquals([], $scrapy->params());
    }

    public function test_adding_parser_by_class_name()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParser(TestParser::class)
            ->build();

        $this->assertIsObject($scrapy->parsers()[0]);
        $this->assertEquals(TestParser::class, get_class($scrapy->parsers()[0]));
    }

    public function test_adding_parser_object()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParser(new TestParser())
            ->build();

        $this->assertIsObject($scrapy->parsers()[0]);
    }

    public function test_adding_multiple_parsers()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParsers([TestParser::class, TestParser::class])
            ->build();

        $this->assertEquals(2, count($scrapy->parsers()));
    }

    public function test_before_scrape_callback_is_set()
    {
        $callback = function () { return 'Called!'; };
        $scrapy = ScrapyBuilder::make()
            ->beforeScrape($callback)
            ->build();

        $this->assertIsCallable($scrapy->beforeScrapeCallback());
        $this->assertEquals('Called!', $scrapy->beforeScrapeCallback()());
    }

    public function test_after_scrape_callback_is_set()
    {
        $callback = function () { return 'Called!'; };
        $scrapy = ScrapyBuilder::make()
            ->afterScrape($callback)
            ->build();

        $this->assertIsCallable($scrapy->afterScrapeCallback());
        $this->assertEquals('Called!', $scrapy->afterScrapeCallback()());
    }
}
