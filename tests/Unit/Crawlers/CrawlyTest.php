<?php

namespace Tests\Unit\Crawly;

use PHPUnit\Framework\TestCase;
use Scrapy\Crawlers\Crawly;

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
        $html = $crawly->filter('div')->html();

        $this->assertEquals('<span>Hello!</span>', $html);
    }

    public function test_html_method_with_default()
    {
        $crawly = new Crawly('');
        $html = $crawly->html();

        $this->assertEquals('', $html);
    }
}
