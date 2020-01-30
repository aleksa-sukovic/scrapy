<?php

namespace Scrapy\Traits;

use DOMNode;

trait HandleDom
{
    /**
     * Returns the inner html string of given DOM node.
     *
     * @param DOMNode $node Source node.
     * @return string Inner HTML of given node.
     */
    public function nodeInnerHtml(DOMNode $node)
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            $result .= $child->ownerDocument->saveXML($child);
        }
        return $result;
    }
}
