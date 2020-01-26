<?php

namespace Scrapy\Tests\Unit\Builders;

use Scrapy\Crawlers\Crawly;
use Scrapy\Parsers\IParser;

class TestParser implements IParser
{
    public function process(Crawly $crawler, &$output, $params)
    {
        //
    }
}
