<?php

namespace Scrapy\Exceptions;

use Exception;

/**
 * Class ScrapeException.
 *
 * Base Scrapy exception class.
 *
 * @package Scrapy\Exceptions
 */
class ScrapeException extends Exception
{
    /**
     * ScrapeException constructor.
     *
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     */
	public function __construct($message = "Scraping failed.", $code = 400)
	{
		parent::__construct($message, $code, null);
	}

    /**
     * Transforms exception to array.
     *
     * @return array Array representation of this exception.
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code'    => $this->code,
        ];
    }
}
