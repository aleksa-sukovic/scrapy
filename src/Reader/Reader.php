<?php

namespace Scrapy\Reader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Uri;
use Scrapy\Exceptions\ScrapeException;

class Reader
{
	/**
	 * @var Client
	 */
	protected $guzzleClient;

	public function __construct()
	{
		$this->guzzleClient = new Client();
	}

	/**
	 * @param $url
	 *     - Url to read.
	 * @return string
	 *     - Content of page represented by this url.
	 * @throws ScrapeException
	 * 	   - When given url could not be read.
	 */
	public function read($url): string
	{
		try {
			$response = $this->guzzleClient->get(new Uri($url), ['synchronous' => true]);

			return (string) $response->getBody();
		} catch (ClientException|ServerException $e) {
			throw new ScrapeException("Url '$url' could not be read.", $this);
		}
	}
}
