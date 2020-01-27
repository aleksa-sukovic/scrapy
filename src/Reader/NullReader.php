<?php

namespace Scrapy\Reader;

class NullReader implements IReader
{
    public function read(): string
    {
        return '';
    }
}
