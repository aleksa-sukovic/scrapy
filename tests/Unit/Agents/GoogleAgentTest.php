<?php

namespace Scrapy\Tests\Unit\Agents;

use Scrapy\Readers\UrlReader;
use Scrapy\Agents\IUserAgent;
use Scrapy\Agents\GoogleAgent;
use PHPUnit\Framework\TestCase;

class GoogleAgentTest extends TestCase
{
    protected $googleUserAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    public function test_it_creates_url_reader()
    {
        $agent = new GoogleAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertInstanceOf(IUserAgent::class, $agent);
        $this->assertInstanceOf(UrlReader::class, $reader);
    }

    public function test_it_has_appropriate_config()
    {
        $agent = new GoogleAgent();
        $reader = $agent->reader('https://www.some-url.com');

        $this->assertArrayHasKey('headers', $reader->config());
        $this->assertArrayHasKey('User-Agent', $reader->config()['headers']);
        $this->assertEquals($this->googleUserAgent, $reader->config()['headers']['User-Agent']);

        $this->assertEquals(true, $reader->config()['synchronous']);
    }
}
