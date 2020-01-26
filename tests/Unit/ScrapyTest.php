<?php

namespace Scrapy\Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use Scrapy\Builders\ScrapyBuilder;
use Scrapy\Crawlers\Crawly;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Parsers\IParser;
use Scrapy\Reader\Reader;

class ScrapyTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $readerMock;
    protected $builder;

    protected function setUp()
    {
        parent::setUp();

        $this->readerMock = Mockery::mock(Reader::class);
        $this->builder = ScrapyBuilder::make()->reader($this->readerMock);
    }

    public function test_scraping_with_single_parser()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div><h1>Hello World!</h1></div>');

        $parser = new class implements IParser {
            public function process(Crawly $crawler, &$output, $params)
            {
                $output['heading'] = $crawler->filter('h1')->first()->string();
            }
        };
        $scrapy = $this->builder->withParser($parser)->build();

        $result = $scrapy->scrape('https://some-url.com');
        $this->assertEquals('Hello World!', $result['heading']);
    }

    public function test_scraping_with_multiple_parsers()
    {
        $this->readerMock->shouldReceive('read')->andReturn('');

        $parser1 = new class implements IParser {
            public function process(Crawly $crawler, &$output, $params) { $output['first'] = 'Hello'; }
        };
        $parser2 = new class implements IParser {
            public function process(Crawly $crawler, &$output, $params) { $output['second'] = 'World'; }
        };

        $result = $this->builder->withParsers([$parser1, $parser2])->build()->scrape('https://www.some-url.com');
        $this->assertEquals('Hello', $result['first']);
        $this->assertEquals('World', $result['second']);
    }

    public function test_before_scrape_callback_modifies_input_html()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div><span>Hello World!</span></div>');
        $scrapy = $this->builder->beforeScrape(function (string $html) {
            $crawly = new Crawly($html);

            return $crawly->filter('div')->html();
        })->build();

        $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals('<span>Hello World!</span>', $scrapy->html());
    }

    public function test_after_scrape_callback_modifies_result()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div>Hello World!</div>');
        $scrapy = $this->builder->withParser(function (Crawly $crawly, &$output, $params) {
            $output['content'] = 'Hello World!';
        })->afterScrape(function ($result) {
            $result['content'] = 'Hello World Changed!';

            return $result;
        })->build();

        $result = $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals('Hello World Changed!', $result['content']);
    }

    public function test_params_are_passed_to_each_parser()
    {
        $this->readerMock->shouldReceive('read')->andReturn('');
        $parser1 = new class implements IParser {
            public function process(Crawly $crawler, &$output, $params) { $output['foo'] = $params['foo']; }
        };

        $scrapy = $this->builder->withParser($parser1)
            ->withParams(['foo' => 'bar'])->build();

        $result = $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals($result['foo'], 'bar');
    }

    public function test_error_handling_method()
    {
        $this->readerMock->shouldReceive('read')->andReturn('');
        $parser1 = new class implements IParser {
            public function process(Crawly $crawler, &$output, $params) { throw new ScrapeException('Random exception.'); }
        };
        $scrapy = $this->builder->withParser($parser1)->build();

        $scrapy->scrape('https://www.some-url.com');
        $this->assertTrue($scrapy->failed());
        $this->assertEquals(1, count($scrapy->errors()));
    }
}
