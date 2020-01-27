<?php

namespace Scrapy\Readers;

/**
 * Class NullReader.
 *
 * Empty implementation if IReader interface, used to avoid null checks.
 *
 * @package Scrapy\Reader
 */
class NullReader implements IReader
{
    public function read(): string
    {
        return '';
    }
}
