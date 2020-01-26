<?php

namespace Scrapy\Crawlers;

use Error;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class Crawly
{
    public static $NODE_VALUE = '_text';
    public static $NODE_NAME  = '_name';

	/**
	 * @var Crawler
	 */
	protected $crawler;

	/**
	 * @var Crawler|mixed
	 */
	protected $activeCrawler;

	/**
	 * @var bool
	 */
	protected $trim;

	public function __construct($html)
	{
        $this->crawler = $this->makeCrawler($html);
        $this->activeCrawler = $this->crawler;
		$this->trim = false;
	}

	public function raw(): Crawler
	{
		return $this->activeCrawler;
	}

	public function first(): Crawly
	{
		$this->activeCrawler = $this->activeCrawler->first();

		return $this;
	}

	public function filter($selector): Crawly
	{
		$this->activeCrawler = $this->crawler->filter($selector);

		return $this;
	}

	public function trim(): Crawly
	{
		$this->trim = true;

		return $this;
	}

	public function pluck($attributes)
	{
		try {
		    $attributes = is_array($attributes) ? $attributes : [$attributes];

			return $this->activeCrawler->extract($attributes)[0];
		} catch (Exception|Error $e) {
			return [];
		}
	}

	public function nth($index): Crawly
	{
		$this->activeCrawler = $this->activeCrawler->eq($index);

		return $this;
	}

	public function count(): int
	{
		$count = 0;

		try {
			foreach ($this->activeCrawler as $node) $count++;
		} catch (Exception $e) {
			//
		} finally {
			return $count;
		}
	}

	public function int($default = 0): int
	{
		if (!is_numeric($this->string())) {
			return $default;
		}

		return (int) $this->string();
	}

	public function float($default = 0.0): float
	{
		if (!is_numeric($this->string())) {
			return $default;
		}

		return floatval($this->string());
	}

	public function string($default = ''): string
	{
		try {
			$value = (string) $this->activeCrawler->text();
			$value = $this->trim ? trim($value) : $value;
			return $value;
		} catch (Exception|Error $e) {
			return $default;
		}
	}

    protected function makeCrawler(string $html): Crawler
    {
        if (empty($html)) {
            return new Crawler('');
        } else if ($this->isHtml($html)) {
            return new Crawler($html);
        } else {
            return (new Crawler("<div>$html</div>"))->filter('div')->first();
        }
    }

    protected function isHtml(string $html): bool
    {
        return strlen(strip_tags($html)) !== strlen($html);
    }
}
