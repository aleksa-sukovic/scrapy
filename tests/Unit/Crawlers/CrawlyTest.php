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
        $crawly = new Crawly(null);

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
}
