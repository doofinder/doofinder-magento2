<?php

namespace Doofinder\Feed\Model\Config\Source\Feed;

/**
 * Price tax mode source
 */
class PriceTaxMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_AUTO = 0;
    const MODE_WITH_TAX = 1;
    const MODE_WITHOUT_TAX = -1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Auto')],
            ['value' => 1, 'label' => __('With Tax')],
            ['value' => -1, 'label' => __('Without Tax')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('Auto'),
            1 => __('With Tax'),
            -1 => __('Without Tax'),
        ];
    }
}
