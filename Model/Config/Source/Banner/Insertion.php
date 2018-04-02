<?php

namespace Doofinder\Feed\Model\Config\Source\Banner;

/**
 * Insertion methods source
 */
class Insertion implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of insertion methods.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'before' => __('Banner before element'),
            'after' => __('Banner after element'),
            'prepend' => __('Banner at the beginning of element'),
            'append' => __('Banner at the end of element'),
            'replace' => __('Replace element with banner'),
        ];
    }
}
