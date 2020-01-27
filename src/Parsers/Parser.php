<?php

namespace Scrapy\Parsers;

abstract class Parser implements IParser
{
    private $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function input(string $key)
    {
        return $this->has($key) ? $this->params[$key] : null;
    }

    public function has(string $key): bool
    {
        return isset($this->params[$key]);
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}
