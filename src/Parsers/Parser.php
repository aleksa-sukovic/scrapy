<?php

namespace Scrapy\Parsers;

/**
 * Class Parser.
 *
 * Base parser class providing utility functions common to all other parsers.
 *
 * @package Scrapy\Parsers
 */
abstract class Parser implements IParser
{
    /** @var array Additional params made available to a parser */
    private $params;

    /**
     * Parser constructor.
     *
     * @param array $params Additional params.
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Returns the param with provided key.
     *
     * @param string $key Name of the param.
     * @return mixed|null Params value if param exists, null otherwise.
     */
    public function param(string $key)
    {
        return $this->has($key) ? $this->params[$key] : null;
    }

    /**
     * Checks if param with given key exists.
     *
     * @param string $key name of the param.
     * @return bool True if param with given key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return isset($this->params[$key]);
    }

    /**
     * Sets the parser's params.
     *
     * @param array $params Associative array of params.
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}
