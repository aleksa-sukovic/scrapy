<?php

namespace Scrapy\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use Scrapy\Crawlers\Crawly;
use Scrapy\Parsers\FunctionParser;

class FunctionParserTest extends TestCase
{
    public function test_callback_is_called()
    {
        $callback = function (Crawly $crawly, array $output, array $params): array {
            $output['foo'] = 'bar';

            return $output;
        };
        $parser = new FunctionParser($callback);

        $result = $parser->process(new Crawly(''), [], []);

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('bar', $result['foo']);
    }
}
