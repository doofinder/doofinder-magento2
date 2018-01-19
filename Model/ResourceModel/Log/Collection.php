<?php

namespace Doofinder\Feed\Model\ResourceModel\Log;

/**
 * Log collection
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
            \Doofinder\Feed\Model\Log::class,
            \Doofinder\Feed\Model\ResourceModel\Log::class
        );
    }
}
