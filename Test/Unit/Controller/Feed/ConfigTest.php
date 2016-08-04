<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

/**
 * Class ConfigTest
 * @package Doofinder\Feed\Test\Unit\Controller\Feed
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Doofinder\Feed\Controller\Feed\Config
     */
    protected $_controller;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_controller = $this->_objectManager->getObject(
            '\Doofinder\Feed\Controller\Feed\Config',
            []
        );
    }

    /**
     * Test execute() method.
     *
     * @todo wait for feed/config controller implementation
     */
    public function testExecute()
    {
        $this->_controller->execute();
    }
}