<?php

namespace Doofinder\Feed\Model\ResourceModel\Cron;

/**
 * Class Collection
 *
 * @package Doofinder\Feed\Model\ResourceModel\Cron
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
            '\Doofinder\Feed\Model\Cron',
            '\Doofinder\Feed\Model\ResourceModel\Cron'
        );
    }
}
