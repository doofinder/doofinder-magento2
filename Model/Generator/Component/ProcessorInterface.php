<?php

namespace Doofinder\Feed\Model\Generator\Component;

interface ProcessorInterface
{
    /**
     * Process generator items
     *
     * @param  \Doofinder\Feed\Model\Generator\Item[] $items
     * @return void
     */
    public function process(array $items);
}
