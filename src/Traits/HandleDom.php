<?php

namespace Scrapy\Traits;

use DOMNode;

trait HandleDom
{
    public function nodeInnerHtml(DOMNode $node)
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            $result .= $child->ownerDocument->saveXML($child);
        }
        return $result;
    }
}
