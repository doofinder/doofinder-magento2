<?php

namespace Doofinder\Feed\Model;

use Magento\Framework\Model\AbstractModel;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResourceModel;

/**
 * Deleted product trace model.
 */
class ChangedProduct extends AbstractModel
{
    /**
     * A constructor.
     *
     * @codingStandardsIgnoreStart Method has to be protected.
     */
    protected function _construct()
    {
        /** @condingStandardsIgnoreEnd */

        $this->_init(ChangedProductResourceModel::class);
    }
}
