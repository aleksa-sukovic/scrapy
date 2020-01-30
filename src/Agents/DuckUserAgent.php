<?php

namespace Scrapy\Agents;

use Scrapy\Readers\UrlReader;

/**
 * Class DuckUserAgent.
 *
 * Simulation of DuckDuckGo user agent.
 *
 * @package Scrapy\Agents
 */
class DuckUserAgent implements IUserAgent
{
    /**
     * Guzzle Client configuration array.
     *
     * @var array
     */
    protected $config = [
        'synchronous' => true,
        'headers' => [
            'User-Agent' => 'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)',
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
