<?php

namespace Scrapy\Parsers;

use Scrapy\Crawlers\Crawly;

/**
 * Interface IParser.
 *
 * Defines single scraping task to be executed.
 *
 * @package Scrapy\Parsers
 */
interface IParser
{
    /**
     * Processes the given html using crawler and updates the output value.
     *
     * @param Crawly $crawler Instance of crawler containing desired html.
     * @param array $output Array representing the current scraping result.
     * @return array Array representing the new scraping result.
     */
	public function process(Crawly $crawler, array $output): array;
}
