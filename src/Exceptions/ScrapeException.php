<?php

namespace Scrapy\Exceptions;

use Exception;

class ScrapeException extends Exception
{
    protected $object;

	public function __construct($message = "Scraping failed.", $code = 400, $object = null)
	{
		parent::__construct($message, $code, null);

		$this->object = $object;
	}

	public function getObject()
    {
        return $this->object;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code'    => $this->code,
            'object'  => $this->object,
        ];
    }
}
