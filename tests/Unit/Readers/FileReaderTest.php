<?php

namespace Scrapy\Tests\Unit\Readers;

use PHPUnit\Framework\TestCase;
use Scrapy\Exceptions\ScrapeException;
use Scrapy\Reader\FileReader;

class FileReaderTest extends TestCase
{
    protected $filePath = __DIR__ . '/test.txt';

    public function tearDown()
    {
        parent::tearDown();

        if (file_exists($this->filePath))
            $this->delete($this->filePath);
    }

    public function test_it_reads_from_file()
    {
        $this->write($this->filePath, '<h1>Hello World!</h1>');
        $reader = new FileReader($this->filePath);

        $this->assertEquals('<h1>Hello World!</h1>', $reader->read());
    }

    public function test_it_throws_exception_if_file_does_not_exists()
    {
        $reader = new FileReader('non-existing-file.txt');

        $this->expectException(ScrapeException::class);

        $reader->read();
    }

    private function write($filePath, $content): void
    {
        $handle = fopen($filePath, 'w');
        fwrite($handle, $content);
        fclose($handle);
    }

    private function delete($filePath): void
    {
        unlink($filePath);
    }
}
