<?php

namespace Scrapy\Tests\Unit;

use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Scrapy\Builders\ScrapyBuilder;
use Scrapy\Crawlers\Crawly;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Parsers\Parser;
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

        $parser = new class extends Parser {
            public function process(Crawly $crawler, array $output): array
            {
                $output['heading'] = $crawler->filter('h1')->first()->string();

                return $output;
            }
        };
        $scrapy = $this->builder->parser($parser)->build();

        $result = $scrapy->scrape('https://some-url.com');
        $this->assertEquals('Hello World!', $result['heading']);
    }

    public function test_scraping_with_multiple_parsers()
    {
        $this->readerMock->shouldReceive('read')->andReturn('');

        $parser1 = new class  extends Parser {
            public function process(Crawly $crawler, array $output): array { $output['first'] = 'Hello'; return $output; }
        };
        $parser2 = new class extends Parser {
            public function process(Crawly $crawler, array $output): array { $output['second'] = 'World'; return $output; }
        };

        $result = $this->builder->parsers([$parser1, $parser2])->build()->scrape('https://www.some-url.com');
        $this->assertEquals('Hello', $result['first']);
        $this->assertEquals('World', $result['second']);
    }

    public function test_scraping_with_function_parser()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div><h1>Hello World!</h1></div>');

        $result = $this->builder->parser(function (Crawly $crawly, array $output): array {
                $output['foo'] = 'bar';

                return $output;
            })
            ->build()
            ->scrape('https://www.some-url.com');

        $this->assertEquals('bar', $result['foo']);
    }

    public function test_params_are_passed_to_function_parser()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div><h1>Hello!</h1></div>');

        $scraper = $this->builder->params(['foo' => 'bar'])
            ->parser(function (Crawly $crawly, $output) {
                $output['foo'] = $this->param('foo');

                return $output;
           })
           ->build();
        $result = $scraper->scrape('https://www.some-url.com');

        $this->assertEquals('bar', $result['foo']);
    }

    public function test_before_scrape_callback_modifies_input_html()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div><span>Hello World!</span></div>');
        $scrapy = $this->builder->beforeScrape(function (string $html) {
            $crawly = new Crawly($html);

            return $crawly->filter('span')->html();
        })->build();

        $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals('<span>Hello World!</span>', $scrapy->html());
    }

    public function test_params_are_passed_to_each_parser()
    {
        $this->readerMock->shouldReceive('read')->andReturn('');
        $parser1 = new class extends Parser {
            public function process(Crawly $crawler, array $output): array {
                $output['foo'] = $this->param('foo');

                return $output;
            }
        };

        $scrapy = $this->builder->parser($parser1)
            ->params(['foo' => 'bar'])->build();

        $result = $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals($result['foo'], 'bar');
    }

    public function test_result_method_returns_value()
    {
        $this->readerMock->shouldReceive('read')->once()->andReturn('<div>Hello!</div>');
        $parser = new class extends Parser{
            public function process(Crawly $crawler, array $output): array
            {
                $output['world'] = $crawler->string();

                return $output;
            }
        };
        $scrapy = $this->builder->parser($parser)->build();

        $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals('Hello!', $scrapy->result()['world']);
    }

    public function test_scraping_throws_exception_from_reader()
    {
        $this->readerMock->shouldReceive('read')->once()->andThrow(ScrapeException::class);

        $this->expectException(ScrapeException::class);

        $this->builder->build()->scrape('https://www.some-url.com');
    }

    public function test_scraping_throws_exception_from_parsers()
    {
        $this->readerMock->shouldReceive('read')->once()->andReturn('');
        $parser = new class extends Parser {
            public function process(Crawly $crawler, array $output): array
            {
                throw new ScrapeException();
            }
        };

        $this->expectException(ScrapeException::class);

        $this->builder->parser($parser)->build()->scrape('https://www.some-url.com');
    }

    public function test_html_checker_terminates_scraping()
    {
        $this->readerMock->shouldReceive('read')->once()->andReturn('');
        $scrapy = $this->builder->htmlChecker(function (Crawly $crawly) {
            return false;
        })->build();

        $this->expectException(ScrapeException::class);

        $scrapy->scrape('https://www.some-url.com');
    }

    public function test_parsers_get_appropriate_crawler()
    {
        $this->readerMock->shouldReceive('read')->once()->andReturn('<div><h1>Hello</h1><h2>World</h2><h3>!</h3></div>');
        $parser1 = new class extends Parser {
            public function process(Crawly $crawler, array $output): array
            {
                $output['first'] = $crawler->filter('h1')->first()->string();
                return $output;
            }
        };
        $parser2 = new class extends Parser {
            public function process(Crawly $crawler, array $output): array
            {
                $output['second'] = $crawler->filter('h2')->first()->string();
                return $output;
            }
        };

        $result = $this->builder->parsers([$parser1, $parser2])->build()->scrape('https://www.some-url.com');

        $this->assertEquals('Hello', $result['first']);
        $this->assertEquals('World', $result['second']);
    }
}
