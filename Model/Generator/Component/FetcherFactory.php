<?php

namespace Doofinder\Feed\Model\Generator\Component;

/**
 * Factory class for fetcher
 */
class FetcherFactory extends \Doofinder\Feed\Model\Generator\ComponentFactory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @param string $componentName
     * @return \Doofinder\Feed\Model\Generator\Component\Fetcher
     */
    public function create(array $data = [], $componentName = '')
    {
        return parent::create($data, 'Fetcher\\' . $componentName);
    }
}
