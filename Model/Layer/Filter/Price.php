<?php

namespace Doofinder\Feed\Model\Layer\Filter;

use Magento\CatalogSearch\Model\Layer\Filter\Price as BasePrice;

/**
 * Class Price
 */
class Price extends BasePrice
{
    /**
     * {@inheritDoc}
     *
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @param boolean $isLast
     *
     * @return float|\Magento\Framework\Phrase
     */
    protected function _renderRangeLabel($fromPrice, $toPrice, $isLast = false)
    {
        /**
         * The third argument was added in Magento 2.4.1, for the previous versions this fix does nothing.
         */
        if (!$isLast && $toPrice === '') {
            $isLast = true;
        }

        return parent::_renderRangeLabel($fromPrice, $toPrice, $isLast);
    }
}
