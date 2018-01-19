<?php

namespace Doofinder\Feed\Model\ResourceModel\Cron;

/**
 * Cron collection
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
            \Doofinder\Feed\Model\Cron::class,
            \Doofinder\Feed\Model\ResourceModel\Cron::class
        );
    }
}
