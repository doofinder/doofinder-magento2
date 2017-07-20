<?php

namespace Doofinder\Feed\Model\ResourceModel\Log;

/**
 * Class Collection
 *
 * @package Doofinder\Feed\Model\ResourceModel\Log
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Object initialization.
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
    // @codingStandardsIgnoreEnd
        $this->_init(
            '\Doofinder\Feed\Model\Log',
            '\Doofinder\Feed\Model\ResourceModel\Log'
        );
    }
}
