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
     */
    protected function _construct()
    {
        $this->_init(
            '\Doofinder\Feed\Model\Cron',
            '\Doofinder\Feed\Model\ResourceModel\Cron'
        );
    }
}