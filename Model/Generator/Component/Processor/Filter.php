<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component\ProcessorInterface;

class Filter implements ProcessorInterface
{
    /**
     * Skip items with no title or description
     *
     * @param \Doofinder\Feed\Model\Generator\Item[] $items
     */
    public function process(array $items)
    {
        foreach ($items as $item) {
            if (!$item->getTitle() && !$item->getDescription()) {
                $item->skip();
            }
        }
    }
}
