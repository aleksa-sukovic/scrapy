<?php

namespace Scrapy\Tests\Unit\Agents;

use Scrapy\Readers\UrlReader;
use PHPUnit\Framework\TestCase;
use Scrapy\Agents\BingUserAgent;

class BingUserAgentTest extends TestCase
{
    protected $agent = 'Mozilla/5.0 (compatible; Bingbot/2.0; +http://www.bing.com/bingbot.htm)';

    public function test_it_creates_url_reader()
    {
        $agent = new BingUserAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertInstanceOf(BingUserAgent::class, $agent);
        $this->assertInstanceOf(UrlReader::class, $reader);
    }

    public function test_it_has_appropriate_config()
    {
        $agent = new BingUserAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertArrayHasKey('headers', $reader->config());
        $this->assertArrayHasKey('User-Agent', $reader->config()['headers']);
        $this->assertEquals($this->agent, $reader->config()['headers']['User-Agent']);

        $this->assertEquals(true, $reader->config()['synchronous']);
    }
}
