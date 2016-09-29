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
     */
    protected function _construct()
    {
        $this->_init('\Doofinder\Feed\Model\ResourceModel\Log');
    }
}
