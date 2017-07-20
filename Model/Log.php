<?php

namespace Doofinder\Feed\Model;

/**
 * Class Log
 *
 * @package Doofinder\Feed\Model
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
        $this->_init('\Doofinder\Feed\Model\ResourceModel\Log');
    }
}
