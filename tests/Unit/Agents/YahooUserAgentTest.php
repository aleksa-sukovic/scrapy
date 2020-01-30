<?php

namespace Scrapy\Tests\Unit\Agents;

use Scrapy\Readers\UrlReader;
use PHPUnit\Framework\TestCase;
use Scrapy\Agents\YahooUserAgent;

class YahooUserAgentTest extends TestCase
{
    protected $agent = 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)';

    public function test_it_creates_url_reader()
    {
        $agent = new YahooUserAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertInstanceOf(YahooUserAgent::class, $agent);
        $this->assertInstanceOf(UrlReader::class, $reader);
    }

    public function test_it_has_appropriate_config()
    {
        $agent = new YahooUserAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertArrayHasKey('headers', $reader->config());
        $this->assertArrayHasKey('User-Agent', $reader->config()['headers']);
        $this->assertEquals($this->agent, $reader->config()['headers']['User-Agent']);

        $this->assertEquals(true, $reader->config()['synchronous']);
    }
}
