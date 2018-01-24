<?php

namespace Doofinder\Feed\Model;

/**
 * Log model
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Object initialization.
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
    // @codingStandardsIgnoreEnd
        $this->_init(\Doofinder\Feed\Model\ResourceModel\Log::class);
    }
}
