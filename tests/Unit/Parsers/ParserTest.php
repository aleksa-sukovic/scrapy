<?php

namespace Scrapy\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use Scrapy\Crawlers\Crawly;
use Scrapy\Parsers\Parser;

class ParserTest extends TestCase
{
    public function test_input_method()
    {
        $parser = new TestParser(['foo' => 'bar']);

        $this->assertEquals('bar', $parser->param('foo'));
    }

    public function test_input_method_returns_null()
    {
        $parser = new TestParser([]);

        $this->assertNull($parser->param('foo'));
    }

    public function test_has_method_returns_true()
    {
        $parser = new TestParser(['foo' => 'bar']);

        $this->assertTrue($parser->has('foo'));
    }

    public function test_has_method_returns_false()
    {
        $parser = new TestParser([]);

        $this->assertFalse($parser->has('foo'));
    }
}

class TestParser extends Parser
{
    public function process(Crawly $crawler, array $output): array
    {
        return $output;
    }
}
