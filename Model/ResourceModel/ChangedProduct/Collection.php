<?php

namespace Doofinder\Feed\Model\ResourceModel\ChangedProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Doofinder\Feed\Model\ChangedProduct as ChangedProductModel;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResourceModel;

/**
 * Collection of deleted product traces.
 */
class Collection extends AbstractCollection
{
    /**
     * Initializes resource model.
     *
     * @return void
     *
     * @codingStandardsIgnoreStart Method has to be protected.
     */
    protected function _construct()
    {
        /** @condingStandardsIgnoreEnd */

        $this->_init(
            ChangedProductModel::class,
            ChangedProductResourceModel::class
        );
    }
}
