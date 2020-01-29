<?php

namespace Scrapy\Tests\Unit\Agents;

use Scrapy\Readers\UrlReader;
use Scrapy\Agents\IUserAgent;
use PHPUnit\Framework\TestCase;
use Scrapy\Agents\GoogleChromeAgent;

class GoogleChromeAgentTest extends TestCase
{
    protected $agent = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/81.0.4043.0‡ Safari/537.36';

    public function test_it_creates_url_reader()
    {
        $agent = new GoogleChromeAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertInstanceOf(IUserAgent::class, $agent);
        $this->assertInstanceOf(UrlReader::class, $reader);
    }

    public function test_it_has_appropriate_config()
    {
        $agent = new GoogleChromeAgent(81, 0, 4043, 0);
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertArrayHasKey('headers', $reader->config());
        $this->assertArrayHasKey('User-Agent', $reader->config()['headers']);
        $this->assertEquals($this->agent, $reader->config()['headers']['User-Agent']);

        $this->assertEquals(true, $reader->config()['synchronous']);
    }
}
