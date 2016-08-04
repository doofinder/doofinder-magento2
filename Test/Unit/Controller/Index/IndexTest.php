<?php

namespace Doofinder\Feed\Test\Unit\Controller\Index;

/**
 * Class IndexTest
 * @package Doofinder\Feed\Test\Unit\Controller\Index
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Doofinder\Feed\Controller\Index\Index
     */
    protected $_controller;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_controller = $this->_objectManager->getObject(
            '\Doofinder\Feed\Controller\Index\Index',
            []
        );
    }

    /**
     * Test execute() method.
     *
     * @todo wait for index/index controller implementation
     */
    public function testExecute()
    {
        $this->_controller->execute();
    }
}