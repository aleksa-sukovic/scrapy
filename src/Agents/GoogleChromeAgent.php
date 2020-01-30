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
class GoogleChromeAgent implements IUserAgent
{
    /**
     * Guzzle Client configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * GoogleChromeAgent constructor from Chromium version numbers.
     */
    public function __construct(int $major, int $minor, int $build, int $patch)
    {
        $this->config = $this->makeConfig($major, $minor, $build, $patch);
    }

    /**
     * Makes Guzzle Client configuration from Chromium version numbers.
     */
    protected function makeConfig(int $major, int $minor, int $build, int $patch): array
    {
        return [
            'synchronous' => true,
            'headers' => [
                'User-Agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/$major.$minor.$build.$patch" . "‡ Safari/537.36",
            ]
        ];
    }

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
