<?php

namespace Scrapy\Tests\Unit\Builders;

use PHPUnit\Framework\TestCase;
use Scrapy\Builders\ScrapyBuilder;
use Scrapy\Crawlers\Crawly;
use Scrapy\Parsers\FunctionParser;
use Scrapy\Parsers\Parser;

class ScrapyBuilderTest extends TestCase
{
    public function test_it_adds_params()
    {
        $scrapy = ScrapyBuilder::make()
            ->params(['foo' => 'bar'])
            ->build();

        $this->assertEquals(['foo' => 'bar'], $scrapy->params());
    }

    public function test_rested_method_reverts_changes()
    {
        $scrapy = ScrapyBuilder::make()
            ->params(['foo' => 'bar'])
            ->reset()
            ->build();

        $this->assertEquals([], $scrapy->params());
    }

    public function test_adding_parser_by_class_name()
    {
        $scrapy = ScrapyBuilder::make()
            ->parser(TestParser::class)
            ->build();

        $this->assertIsObject($scrapy->parsers()[0]);
        $this->assertEquals(TestParser::class, get_class($scrapy->parsers()[0]));
    }

    public function test_adding_parser_object()
    {
        $scrapy = ScrapyBuilder::make()
            ->parser(new TestParser())
            ->build();

        $this->assertIsObject($scrapy->parsers()[0]);
    }

    public function test_adding_multiple_parsers()
    {
        $scrapy = ScrapyBuilder::make()
            ->parsers([TestParser::class, TestParser::class])
            ->build();

        $this->assertEquals(2, count($scrapy->parsers()));
    }

    public function test_adding_parser_as_a_function()
    {
        $scrapy = ScrapyBuilder::make()
            ->parser(function (Crawly $crawly, &$output, $params) {
                //
            })
            ->build();

        $this->assertEquals(1, count($scrapy->parsers()));
        $this->assertInstanceOf(FunctionParser::class, $scrapy->parsers()[0]);
    }

    public function test_validity_checker_callback_is_set()
    {
        $checker = function () { return 'Called!'; };
        $scrapy = ScrapyBuilder::make()
            ->htmlChecker($checker)
            ->build();

        $this->assertIsCallable($scrapy->validityChecker());
        $this->assertEquals('Called!', $scrapy->validityChecker()());
    }
}

class TestParser extends Parser
{
    public function process(Crawly $crawler, array $output): array
    {
        return $output;
    }
}
