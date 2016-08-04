<?php

namespace Doofinder\Feed\Model\Generator\Component;

interface Processor
{
    /**
     * Process generator items
     *
     * @param Doofinder\Feed\Model\Generator\Item[]
     */
    public function process(array $items);
}
