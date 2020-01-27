<?php

namespace Scrapy\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use Scrapy\Crawlers\Crawly;
use Scrapy\Parsers\FunctionParser;

class FunctionParserTest extends TestCase
{
    public function test_callback_is_called()
    {
        $callback = function (Crawly $crawly, array $output): array {
            $output['foo'] = 'bar';

            return $output;
        };
        $parser = new FunctionParser($callback);

        $result = $parser->process(new Crawly(''), []);

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('bar', $result['foo']);
    }

    public function test_this_pointer_is_bound_to_callback()
    {
        $callback = function (Crawly $crawly, array $output): array {
            $output['foo'] = $this->input('foo');

            return $output;
        };
        $parser = new FunctionParser($callback, ['foo' => 'bar']);

        $result = $parser->process(new Crawly(''), []);

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('bar', $result['foo']);
    }
}
