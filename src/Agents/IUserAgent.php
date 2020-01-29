<?php

namespace Scrapy\Agents;

use Scrapy\Readers\UrlReader;

/**
 * Interface IUserAgent.
 *
 * Allows emulation of different user agents.
 *
 * @package Scrapy\Agents
 */
interface IUserAgent
{
    /**
     * Returns a UrlReader configured to behave as this user agent.
     *
     * @param string $url Url to read.
     * @return UrlReader Instance of UrlReader which behaves as this user agent.
     */
    public function reader(string $url): UrlReader;
}
