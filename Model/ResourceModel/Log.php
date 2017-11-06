<?php

namespace Doofinder\Feed\Model\ResourceModel;

/**
 * Class Log
 *
 * @package Doofinder\Feed\Model\ResourceModel
 */
class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Object initialization.
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
    // @codingStandardsIgnoreEnd
        $this->_init('doofinder_feed_log', 'entity_id');
    }
}
