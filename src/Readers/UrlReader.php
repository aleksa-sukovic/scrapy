<?php

namespace Scrapy\Readers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Scrapy\Exceptions\ScrapeException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Class UrlReader.
 *
 * Reads the contents of given url and returns string representation.
 *
 * @package Scrapy\Readers
 */
class UrlReader implements IReader
{
    /** @var Client */
    protected $guzzleClient;

    /** @var string */
    protected $url;

    /**
     * UrlReader constructor.
     *
     * @param string $url A url to read.
     */
    public function __construct(string $url)
    {
        $this->guzzleClient = new Client();
        $this->url = $url;
    }

    /**
     * Makes HTTP GET request to provided url and returns the response.
     *
     * @return string
     * @throws ScrapeException In case any errors occur during the transfer.
     */
    public function read(): string
    {
        try {
            $response = $this->guzzleClient->get(new Uri($this->url), ['synchronous' => true]);

            return (string) $response->getBody();
        } catch (ClientException|ServerException $e) {
            throw new ScrapeException("Url '$this->url' could not be read.", $this);
        }
    }

    /**
     * Injects the GuzzleHttp client.
     *
     * @param Client $client GuzzleHttp client.
     */
    public function setClient(Client $client): void
    {
        $this->guzzleClient = $client;
    }
}
