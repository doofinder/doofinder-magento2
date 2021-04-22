<?php

namespace Doofinder\Feed\Model\Config\Source\Indices;

use Doofinder\Feed\Helper\StoreConfig;

/**
 * Update modes
 */
class UpdateMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of insertion methods.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            StoreConfig::DOOFINDER_INDICES_UPDATE_API => __('Doofinder API'),
            StoreConfig::DOOFINDER_INDICES_UPDATE_FEED => __('Feed'),
        ];
    }
}
