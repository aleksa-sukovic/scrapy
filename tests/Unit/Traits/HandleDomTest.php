<?php

namespace Scrapy\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;
use Scrapy\Crawlers\Crawly;
use Scrapy\Traits\HandleDom;

class HandleDomTest extends TestCase
{
    use HandleDom;

    public function test_inner_html_on_node_with_children()
    {
        $crawly = new Crawly('<div><ul><li>1</li></ul></div>');
        $node   = $crawly->filter('div')->node();

        $this->assertEquals(
            '<ul><li>1</li></ul>',
            $this->nodeInnerHtml($node)
        );
    }

    public function test_inner_html_on_node_with_no_children()
    {
        $crawly = new Crawly('<div><ul></ul></div>');
        $node   = $crawly->filter('ul')->node();

        $this->assertEmpty($this->nodeInnerHtml($node));
    }

    public function test_inner_html_on_text_node()
    {
        $crawly = new Crawly('<div><span>Hello World!</span></div>');
        $node   = $crawly->filter('span')->node();

        $this->assertEquals('Hello World!', $this->nodeInnerHtml($node));
    }
}
