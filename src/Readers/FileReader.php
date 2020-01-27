<?php

namespace Scrapy\Readers;

use Scrapy\Exceptions\ScrapeException;

/**
 * Class FileReader.
 *
 * Reads contents of a file into a string.
 *
 * @package Scrapy\Readers
 */
class FileReader implements IReader
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * FileReader constructor.
     *
     * @param string $filePath Represents full path to a file.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Reads the contents of specified file into a string.
     *
     * @return string Contents of a specified file.
     * @throws ScrapeException When file does not exists.
     */
    public function read(): string
    {
        if (!file_exists($this->filePath)) {
            throw new ScrapeException("File '$this->filePath' does not exists.");
        }
        return file_get_contents($this->filePath);
    }
}
