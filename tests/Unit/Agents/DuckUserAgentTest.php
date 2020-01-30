<?php

namespace Scrapy\Tests\Unit\Agents;

use Scrapy\Readers\UrlReader;
use PHPUnit\Framework\TestCase;
use Scrapy\Agents\DuckUserAgent;

class DuckUserAgentTest extends TestCase
{
    protected $agent = 'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)';

    public function test_it_creates_url_reader()
    {
        $agent = new DuckUserAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertInstanceOf(DuckUserAgent::class, $agent);
        $this->assertInstanceOf(UrlReader::class, $reader);
    }

    public function test_it_has_appropriate_config()
    {
        $agent = new DuckUserAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertArrayHasKey('headers', $reader->config());
        $this->assertArrayHasKey('User-Agent', $reader->config()['headers']);
        $this->assertEquals($this->agent, $reader->config()['headers']['User-Agent']);

        $this->assertEquals(true, $reader->config()['synchronous']);
    }
}
