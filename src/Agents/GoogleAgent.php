<?php

namespace Scrapy\Agents;

use Scrapy\Readers\UrlReader;

/**
 * Class GoogleAgent.
 *
 * Simulation of Google user agent.
 *
 * @package Scrapy\Agents
 */
class GoogleAgent implements IUserAgent
{
    /**
     * Guzzle Client configuration array.
     *
     * @var array
     */
    protected $config = [
        'synchronous' => true,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
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
