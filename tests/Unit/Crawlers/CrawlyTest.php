<?php

namespace Tests\Unit\Crawly;

use PHPUnit\Framework\TestCase;
use Scrapy\Crawlers\Crawly;
use Scrapy\Exceptions\ScrapeException;

class CrawlyTest extends TestCase
{
    public function test_count()
    {
        $crawly = new Crawly('<div><h1>Hello!</h1><p>World!</p></div>');

        $crawly->filter('p');

        $this->assertEquals(1, $crawly->count());
    }

    public function test_count_with_no_input()
    {
        $crawly = new Crawly('');

        $this->assertEquals(0, $crawly->count());
    }

    public function test_int()
    {
        $crawly = new Crawly('<div>15.25</div>');

        $this->assertEquals(15, $crawly->filter('div')->int());
    }

    public function test_int_with_default()
    {
        $crawly = new Crawly('<div>NaN</div>');

        $this->assertEquals(20, $crawly->filter('div')->int(20));
    }

    public function test_float_with_default()
    {
        $crawly = new Crawly('<div>Not a number</div>');

        $this->assertEquals(0.75, $crawly->filter('div')->float(0.75));
    }

    public function test_first_method_return_value()
    {
        $crawly = new Crawly('<div><h1>First</h1><h1>Second</h1></div>');

        $this->assertEquals('First', $crawly->filter('h1')->first()->string());
    }

    public function test_pluck_method()
    {
        $crawly = new Crawly('<div><a href="https://www.google.com">Google</a></div>');

        $this->assertEquals(
            'https://www.google.com',
            $crawly->filter('a')->pluck('href')
        );
    }

    public function test_pluck_method_with_multiple_values()
    {
        $crawly = new Crawly('<div><img width="200" height="300"></div>');

        $attributes = $crawly->filter('img')->pluck(['width', 'height']);

        $this->assertEquals(200, $attributes[0]);
        $this->assertEquals(300, $attributes[1]);
    }

    public function test_pluck_method_with_special_values()
    {
        $crawly = new Crawly('<div><a target="_blank">Link</a></div>');

        $attributes = $crawly->filter('a')->pluck(['target', Crawly::$NODE_VALUE, Crawly::$NODE_NAME]);

        $this->assertEquals('_blank', $attributes[0]);
        $this->assertEquals('Link', $attributes[1]);
        $this->assertEquals('a', $attributes[2]);
    }

    public function test_trim_method()
    {
        $crawly = new Crawly('<div>     Hello        world</div>');

        $this->assertEquals('Hello world', $crawly->filter('div')->trim()->string());
    }

    public function test_at_method()
    {
        $crawly = new Crawly('<ul><li>1</li><li>2</li><li>3</li><li>4</li></ul>');

        $this->assertEquals(3, $crawly->filter('li')->nth(2)->int());
    }

    public function test_raw_method()
    {
        $crawly = new Crawly('<ul><li>1</li><li>2</li><li>3</li><li>4</li></ul>');

        $this->assertEquals(
            '1',
            $crawly->filter('li')->raw()->first()->text()
        );
    }

    public function test_plain_text_is_parsed_as_html()
    {
        $crawly = new Crawly('5.5');

        $this->assertEquals(5.5, $crawly->float());
    }

    public function test_single_element_is_parsed()
    {
        $crawly = new Crawly('<div>Some element</div>');

        $this->assertEquals('Some element', $crawly->string());
    }

    public function test_html_method()
    {
        $crawly = new Crawly('<div><span>Hello!</span></div>');
        $html = $crawly->filter('span')->html();

        $this->assertEquals('<span>Hello!</span>', $html);
    }

    public function test_html_method_with_default()
    {
        $crawly = new Crawly('');
        $html = $crawly->html();

        $this->assertEquals('', $html);
    }

    public function test_exists_method_returns_true()
    {
        $crawly1 = new Crawly('<div><span>Hello World!</span></div>');
        $crawly2 = new Crawly('<div><li>1</li><li><span>2</span></li><li>3</li></div>');

        $this->assertTrue($crawly1->filter('span')->exists());
        $this->assertTrue($crawly2->filter('li')->nth(1)->filter('span')->exists());
    }

    public function test_exists_method_returns_false()
    {
        $crawly1 = new Crawly('<div>Hello World!</div>');
        $crawly2 = new Crawly('<ul><li>1</li><li>2</li><li>3</li></ul>');

        $this->assertFalse($crawly1->filter('span')->exists());
        $this->assertFalse($crawly2->filter('li')->nth(2)->filter('span')->exists());
        $this->assertFalse($crawly2->filter('div')->filter('.example-class')->nth(5)->exists());
    }

    public function test_exists_method_throws_exception()
    {
        $this->expectException(ScrapeException::class);
        $crawly = new Crawly('<div>Hello World!</div>');

        $crawly->filter('h1')->filter('span')->exists(true);
    }

    public function test_reset_method()
    {
        $crawly = new Crawly('<div><ul><li>1</li></ul></div>');

        $crawly->filter('li')->first();
        $crawly->reset();

        $this->assertEquals('<html><body><div><ul><li>1</li></ul></div></body></html>', $crawly->html());
    }

    public function test_inner_html_method()
    {
        $crawly = new Crawly('<div><ul><li>1</li><li>2</li></ul></div>');

        $html = $crawly->filter('ul')->innerHtml();

        $this->assertEquals('<li>1</li><li>2</li>', $html);
    }

    public function test_each_method()
    {
        $crawly = new Crawly('<div><li>1</li><li>2</li><li>3</li></div>');

        $result = $crawly->filter('li')->each(function (Crawly $item) {
            return $item->int();
        });

        $this->assertEquals([1, 2, 3], $result);
    }

    public function test_each_method_on_selection_with_no_children()
    {
        $crawly = new Crawly('<div><span></span></div>');

        $result = $crawly->filter('nope')->each(function (Crawly $item) {
            return [];
        });

        $this->assertEmpty($result);
    }

    public function test_each_method_on_empty_selection()
    {
        $crawly = new Crawly('<div><ul><li>1</li><li>2</li></ul></div>');

        $result = $crawly->each(function (Crawly $item) {
            return $item->count();
        });

        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result[0]);
    }

    public function test_each_method_with_limit()
    {
        $crawly = new Crawly('<div><ul><li>1</li></ul></div>');

        $result = $crawly->filter('li')->each(function (Crawly $item) {
            return $item->int() === 0 ? 1 : $item->int();
        }, 3);

        $this->assertEquals([1, 1, 1], $result);
    }

    public function test_each_method_with_limit_lower_than_number_of_nodes()
    {
        $crawly = new Crawly('<ul><li>11</li><li>22</li><li>33</li></ul>');

        $result = $crawly->filter('li')->each(function (Crawly $item) {
            return $item->int();
        }, 1);

        $this->assertEquals([11], $result);
    }
}
