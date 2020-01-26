<?php

namespace Scrapy\Parsers;

use Scrapy\Crawlers\Crawly;

interface IParser
{
	public function process(Crawly $crawler, &$output, $params);
}
