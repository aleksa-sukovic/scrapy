<?php

namespace Scrapy\Readers;

/**
 * Interface IReader.
 *
 * Defines reading action of source into a string.
 *
 * @package Scrapy\Readers
 */
interface IReader
{
    /**
     * Returns read source as string.
     *
     * @return string
     */
    public function read(): string;
}
