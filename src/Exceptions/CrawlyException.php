<?php

namespace Scrapy\Exceptions;

class CrawlyException extends ScrapeException
{
    public function __construct($message = "Crawling failed.", $code = 400)
    {
        parent::__construct($message, $code);
    }
}
