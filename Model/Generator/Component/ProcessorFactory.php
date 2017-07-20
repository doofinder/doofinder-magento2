<?php

namespace Doofinder\Feed\Model\Generator\Component;

/**
 * Factory class for processor
 */
class ProcessorFactory extends \Doofinder\Feed\Model\Generator\ComponentFactory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @param string $componentName
     * @return \Doofinder\Feed\Model\Generator\Component\Processor
     */
    public function create(array $data = [], $componentName = '')
    {
        return parent::create($data, 'Processor\\' . $componentName);
    }
}
