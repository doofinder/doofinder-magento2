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
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ChangedProductResourceModel::class);
    }
}
