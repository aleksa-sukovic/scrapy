<?php

namespace Scrapy\Tests;

use PHPUnit\Framework\TestCase;
use Scrapy\Scrapy;

class ExampleTest extends TestCase
{
    public function test_true_is_true()
    {
        $scrapy = new Scrapy();

        $this->assertEquals('It works!', $scrapy->scrape());
    }
}
