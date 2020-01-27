<?php

namespace Scrapy\Reader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Uri;
use Scrapy\Exceptions\ScrapeException;

class UrlReader implements IReader
{
	/**
	 * @var Client
	 */
	protected $guzzleClient;

    /**
     * @var string
     */
	protected $url;

	public function __construct(string $url)
	{
		$this->guzzleClient = new Client();
		$this->url = $url;
	}

	public function read(): string
	{
		try {
			$response = $this->guzzleClient->get(new Uri($this->url), ['synchronous' => true]);

			return (string) $response->getBody();
		} catch (ClientException|ServerException $e) {
			throw new ScrapeException("Url '$this->url' could not be read.", $this);
		}
	}

	public function setClient(Client $client): void
    {
        $this->guzzleClient = $client;
    }
}
