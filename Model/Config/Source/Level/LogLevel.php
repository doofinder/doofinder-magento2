<?php

namespace Doofinder\Feed\Model\Config\Source\Level;

/**
 * Log severity levels
 */
class LogLevel implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of levels.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return 
        [
            0 => __('No logging'),
            1 => __('Log information only'),
            2 => __('Log errors only'),
            3 => __('Log both info and errors'),
        ];
    }
}
