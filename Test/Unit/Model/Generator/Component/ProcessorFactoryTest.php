<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ProcessorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\ProcessorFactory
     */
    private $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = $this->getMock(
            '\Magento\Framework\ObjectManagerInterface',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->getMock(
            'Doofinder\Feed\Model\Generator\Component\ProcessorFactory',
            null,
            [
                'objectManager' => $this->_objectManager,
                'instanceName' => '\Test\Unit\Doofinder\Feed'
            ]
        );
    }

    /**
     * Test create
     */
    public function testCreate()
    {
        $this->_objectManager->expects($this->once())->method('create')
            ->with('\Test\Unit\Doofinder\Feed\Processor\Test', ['sample' => 'data']);

        $this->_model->create(['sample' => 'data'], 'Test');
    }
}
