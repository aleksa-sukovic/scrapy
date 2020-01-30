<?php

namespace Scrapy\Agents;

use Scrapy\Readers\UrlReader;

/**
 * Class BingUserAgent.
 *
 * Simulation of Bing user agent.
 *
 * @package Scrapy\Agents
 */
class BingUserAgent implements IUserAgent
{
    /**
     * Guzzle Client configuration array.
     *
     * @var array
     */
    protected $config = [
        'synchronous' => true,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (compatible; Bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        ]
    ];

    /**
     * Returns a UrlReader configured to behave as Google user agent.
     *
     * @param string $url Url to read.
     * @return UrlReader Instance of UrlReader which behaves as Google user agent.
     */
    public function reader(string $url): UrlReader
    {
        $reader = new UrlReader($url);
        $reader->setConfig($this->config);
        return $reader;
    }
}
