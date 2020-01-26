<?php

namespace Scrapy\Exceptions;

use Exception;

class ScrapeException extends Exception
{
	public function __construct($message = "Scraping failed.", $code = 400)
	{
		parent::__construct($message, $code, null);
	}
}
