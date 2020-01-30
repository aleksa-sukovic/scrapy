<?php

namespace Scrapy\Crawlers;

use DOMNode;
use Error;
use Exception;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Traits\HandleCallable;
use Scrapy\Traits\HandleDom;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Crawly.
 *
 * Class for traversal and crawling of html.
 *
 * @package Scrapy\Crawlers
 */
class Crawly
{
    use HandleCallable,
        HandleDom;

    /** @var string Constant for extracting node value. */
    public static $NODE_VALUE = '_text';

    /** @var string Constant for extracting node tag name. */
    public static $NODE_NAME  = '_name';

	/** @var Crawler Instance of Symphony crawler. */
	protected $crawler;

	/** @var Crawler|mixed Instance of modified and currently active Symphony crawler. */
	protected $activeCrawler;

	/** @var bool Weather the current string selection should be trimmed. */
	protected $trim;

    /** @var string Represents the current html being crawler. */
	protected $html;

    /**
     * Crawly constructor.
     *
     * @param $html string String representation of html to be crawled.
     */
	public function __construct(string $html)
	{
        $this->crawler = $this->makeCrawler($html);
        $this->activeCrawler = $this->crawler;
		$this->trim = false;
		$this->html = $html;
	}

    /**
     * Exposes access to raw Symphony crawler.
     *
     * @return Crawler
     */
	public function raw(): Crawler
	{
		return $this->activeCrawler;
	}

    /**
     * Returns the first node of current selection.
     *
     * @return Crawly
     */
	public function first(): Crawly
	{
		$this->activeCrawler = $this->activeCrawler->first();

		return $this;
	}

    /**
     * Filters current selection using given CSS selector.
     *
     * @param $selector string CSS selector.
     * @return Crawly
     */
	public function filter(string $selector): Crawly
	{
		$this->activeCrawler = $this->crawler->filter($selector);

		return $this;
	}

    /**
     * Indicate that string representation of current selection should be trimmed.
     *
     * @return Crawly
     */
	public function trim(): Crawly
	{
		$this->trim = true;

		return $this;
	}

    /**
     * Extracts the array of attribute values from current selected nodes.
     *
     * @param $attributes string|array<string> Single attribute name or array of attribute names
     * @return array|mixed A single attribute value if attribute name is provided or array of requested values.
     */
	public function pluck($attributes)
	{
		try {
			$result = $this->activeCrawler->extract($attributes);

			return count($result) > 1 ? $result : $result[0];
		} catch (Exception|Error $e) {
			return [];
		}
	}

    /**
     * Returns the node in current selection at given position.
     *
     * Indexing starts at 0.
     *
     * @param $position int Position of node to be returned.
     * @return Crawly
     */
	public function nth(int $position): Crawly
	{
		$this->activeCrawler = $this->activeCrawler->eq($position);

		return $this;
	}

    /**
     * Returns the count of nodes in current selection.
     *
     * @return int
     */
	public function count(): int
	{
		$count = 0;

		try {
			foreach ($this->activeCrawler as $node) $count++;
		} catch (Exception|Error $e) {
			//
		} finally {
			return $count;
		}
	}

    /**
     * Returns the int value of current selection.
     *
     * @param int $default [optional] Value to be used in case current selection is not numeric.
     * @return int Integer representation of current selection.
     */
	public function int($default = 0): int
	{
		if (!is_numeric($this->string())) {
			return $default;
		}

		return (int) $this->string();
	}

    /**
     * Returns the float value of current selection.
     *
     * @param float $default [optional] Value to be used in case current selection is not numeric;
     * @return float Float representation of current selection.
     */
	public function float($default = 0.0): float
	{
		if (!is_numeric($this->string())) {
			return $default;
		}

		return floatval($this->string());
	}

    /**
     * Returns the string value of current selection.
     *
     * @param string $default [optional] Value to be used in case current selection is not processable.
     * @return string String representation of current selection.
     */
	public function string($default = ''): string
	{
		try {
			$value = (string) $this->activeCrawler->text();
			$value = $this->trim ? trim(preg_replace('/\s+/', ' ', $value)) : $value;
			return $value;
		} catch (Exception|Error $e) {
			return $default;
		}
	}

    /**
     * Returns the html string representation of current selection.
     *
     * @param string $default [optional] Value to be used in case current selection is not processable.t
     * @return string HTML representation of current selection.
     */
	public function html($default = ''): string
    {
        try {
            return $this->activeCrawler->outerHtml();
        } catch (Exception|Error $e) {
            return $default;
        }
    }

    /*
     * Returns the html string representation of current selection, excluding the parent element.
     *
     * @param string $default [optional] Value to be used in case current selection is not processable.t
     * @return string HTML representation of current selection excluding the parent element.
     */
    public function innerHtml($default = ''): string
    {
        try {
            return $this->nodeInnerHtml($this->node());
        } catch (Exception|Error $e) {
            return $default;
        }
    }

    /**
     * Determines if current selection exists.
     *
     * @param bool $throw [optional] Determines if method should throw the exception.
     * @return bool True if current selection exists, false otherwise.
     * @throws ScrapeException If current selection does not exists and throw parameter is equal to true.
     */
    public function exists($throw = false): bool
    {
        $exists = false;

        try {
            $exists = $this->activeCrawler->text();
        } catch (Exception|Error $e) {
            $exists = false;
        } finally {
            if (!$exists && $throw) throw new ScrapeException('Selection does not exists.');

            return $exists;
        }
    }

    /**
     * Resets the crawler, clearing all selections and manipulations.
     */
    public function reset(): void
    {
        $this->crawler = new Crawler($this->html);
        $this->activeCrawler = $this->crawler;
    }

    /**
     * Maps each node of current selection to a result of passed-in function.
     *
     * @param callable $function(Crawly $crawly, int $index) Map callback function.
     * @param null $limit Maximum number of time a $function callback will be triggered.
     *                    If number of nodes is less than limit, instance of Crawly created
     *                    with empty string will be passed in.
     * @return array Array containing result of each $function call.
     */
    public function map(callable $function, $limit = null): array
    {
        try {
            $result = [];
            $limit  = $limit ?? $this->count();

            for ($i = 0; $i < $limit; $i++) {
                $node = $this->activeCrawler->getNode($i);
                $crawler = new Crawly($node ? $this->nodeInnerHtml($node) : '');

                $item = $this->callFunction($function, $crawler, $i);
                if ($item) $result[] = $item;
            }

            return $result;
        } catch (Exception|Error $e) {
            return [];
        }
    }

    /**
     * Returns first DOMNode of current selection.
     *
     * @return DOMNode|null
     */
    public function node(): ?DOMNode
    {
        try {
            return $this->activeCrawler->getNode(0);
        } catch (Exception|Error $e) {
            return null;
        }
    }

    /**
     * Makes Symphony Crawler instance from given html string.
     *
     * @param string $html String representing HTML.
     * @return Crawler
     */
    protected function makeCrawler(string $html): Crawler
    {
        if (empty($html)) {
            return new Crawler('');
        } else if ($this->isHtml($html)) {
            return new Crawler($html);
        } else {
            return (new Crawler("<html><body>$html</body></html>"))->filter('body')->first();
        }
    }

    /**
     * Determines if given string is html.
     *
     * @param string $html String representing HTML.
     * @return bool True if given string is HTML, false otherwise.
     */
    protected function isHtml(string $html): bool
    {
        return strlen(strip_tags($html)) !== strlen($html);
    }
}
