<?php

namespace Doofinder\Feed\Model;

/**
 * Class Cron
 *
 * @package Doofinder\Feed\Model
 */
class Cron extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Object initialization.
     */
    protected function _construct()
    {
        $this->_init('\Doofinder\Feed\Model\ResourceModel\Cron');
    }
}