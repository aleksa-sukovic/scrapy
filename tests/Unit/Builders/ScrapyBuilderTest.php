<?php

namespace Scrapy\Tests\Unit\Builders;

use PHPUnit\Framework\TestCase;
use Scrapy\Builders\ScrapyBuilder;

class ScrapyBuilderTest extends TestCase
{
    public function test_it_adds_params()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParams(['foo' => 'bar'])
            ->build();

        $this->assertEquals(['foo' => 'bar'], $scrapy->params());
    }

    public function test_rested_method_reverts_changes()
    {
        $scrapy = ScrapyBuilder::make()
            ->withParams(['foo' => 'bar'])
            ->reset()
            ->build();

        $this->assertEquals([], $scrapy->params());
    }
}
