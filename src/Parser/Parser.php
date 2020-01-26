<?php

namespace Scrapy\Parsers;

use Scrapy\Crawlers\Crawly;

abstract class Parser implements IParser
{
	protected function count(Crawly $crawly): int
	{
		$count = 0;

		foreach ($crawly->raw() as $node) {
			$count++;
		}

		return $count;
	}
}
