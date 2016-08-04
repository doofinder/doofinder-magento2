<?php

namespace Doofinder\Feed\Model\Generator\Component;

interface Fetcher
{
    /**
     * Fetch generator items
     *
     * @return Item[]
     */
    public function fetch();
}
