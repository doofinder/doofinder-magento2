<?php

namespace Doofinder\Feed\Model\ResourceModel;

/**
 * Class Cron
 *
 * @package Doofinder\Feed\Model\ResourceModel
 */
class Cron extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Object initialization.
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
    // @codingStandardsIgnoreEnd
        $this->_init('doofinder_feed_cron', 'entity_id');
    }
}
