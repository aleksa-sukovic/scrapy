<?php

namespace Scrapy\Tests\Unit\Readers;

use GuzzleHttp\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Scrapy\Readers\UrlReader;

class UrlReaderTest extends TestCase
{
    public function test_it_reads_from_url()
    {
        $guzzle = Mockery::mock(Client::class);
        $responseMock = Mockery::mock(ResponseInterface::class);
        $reader = new UrlReader('https://www.some-url.com');
        $reader->setClient($guzzle);

        $responseMock->shouldReceive('getBody')->once()->andReturn('<div>Hello World!</div>');
        $guzzle->shouldReceive('get')->once()->andReturn($responseMock);
        $this->assertEquals('<div>Hello World!</div>', $reader->read());
    }
}
