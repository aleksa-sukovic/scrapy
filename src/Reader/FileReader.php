<?php

namespace Scrapy\Reader;

use Scrapy\Exceptions\ScrapeException;

class FileReader implements IReader
{
    /**
     * @var string
     */
    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function read(): string
    {
        if (!file_exists($this->filePath)) {
            throw new ScrapeException("File '$this->filePath' does not exists.");
        }
        return file_get_contents($this->filePath);
    }
}
