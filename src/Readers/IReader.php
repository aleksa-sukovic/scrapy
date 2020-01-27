<?php

namespace Scrapy\Readers;

/**
 * Interface IReader.
 *
 * Defines reading of a content into a string.
 *
 * @package Scrapy\Readers
 */
interface IReader
{
    public function read(): string;
}
