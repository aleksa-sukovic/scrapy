<?php

namespace Scrapy\Tests\Unit;

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
        $scrapy = $this->builder->withParser($parser)->build();

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

        $result = $this->builder->withParsers([$parser1, $parser2])->build()->scrape('https://www.some-url.com');
        $this->assertEquals('Hello', $result['first']);
        $this->assertEquals('World', $result['second']);
    }

    public function test_scraping_with_function_parser()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div><h1>Hello World!</h1></div>');

        $result = $this->builder->withParser(function (Crawly $crawly, array $output): array {
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

        $scraper = $this->builder->withParams(['foo' => 'bar'])
            ->withParser(function (Crawly $crawly, $output) {
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

    public function test_after_scrape_callback_modifies_result()
    {
        $this->readerMock->shouldReceive('read')->andReturn('<div>Hello World!</div>');
        $scrapy = $this->builder->withParser(function (Crawly $crawly, &$output) {
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
        $parser1 = new class extends Parser {
            public function process(Crawly $crawler, array $output): array {
                $output['foo'] = $this->param('foo');

                return $output;
            }
        };

        $scrapy = $this->builder->withParser($parser1)
            ->withParams(['foo' => 'bar'])->build();

        $result = $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals($result['foo'], 'bar');
    }

    public function test_error_handling_method()
    {
        $this->readerMock->shouldReceive('read')->andReturn('');
        $parser = new class extends Parser {
            public function process(Crawly $crawler, array $output): array { throw new ScrapeException('Random exception.'); }
        };
        $scrapy = $this->builder->withParser($parser)->build();

        $scrapy->scrape('https://www.some-url.com');
        $this->assertTrue($scrapy->failed());
        $this->assertCount(1, $scrapy->errors());
    }

    public function test_parser_error_callback_is_triggered()
    {
        $that = $this;
        $this->readerMock->shouldReceive('read')->andReturn('');
        $parser = new class extends Parser {
            public function process(Crawly $crawler, array $output): array { throw new ScrapeException('Random parsing exception.'); }
        };

       $this->builder->withParser($parser)
            ->onParseError(function (Parser $parser) use ($that) {
                $that->assertTrue(true);
            })
           ->build()
           ->scrape('https://www.some-url.com');
    }

    public function test_validity_checker_triggers_callback()
    {
        $this->readerMock->shouldReceive('read')->once()->andReturn('');
        $scrapy = $this->builder
            ->withParsers([])
            ->valid(function (Crawly $crawler): bool {
                return false;
            })
            ->onFail(function ($output) {
                $output['foo'] = 'bar';
                return $output;
            })
            ->build();

        $result = $scrapy->scrape('https://www.some-url.com');
        $this->assertTrue($scrapy->failed());
        $this->assertEquals('bar', $result['foo']);
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
        $scrapy = $this->builder->withParser($parser)->build();

        $scrapy->scrape('https://www.some-url.com');
        $this->assertEquals('Hello!', $scrapy->result()['world']);
    }
}
