<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

/**
 * Class IndexTest
 * @package Doofinder\Feed\Test\Unit\Controller\Feed
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_contextMock;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonFactoryMock;
    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helperDataMock;
    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    protected $_generatorFactoryMock;
    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    protected $_generatorMock;
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    protected $_xmlMock;
    /**
     * @var Magento\Framework\App\Response\Http
     */
    protected $_responseMock;
    /**
     * @var \Doofinder\Feed\Controller\Feed\Index
     */
    protected $_controller;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_feedConfigMock;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestMock;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_contextMock = $this->getMock(
            '\Magento\Framework\App\Action\Context',
            [],
            [],
            '',
            false
        );

        $this->_jsonFactoryMock = $this->getMock(
            '\Magento\Framework\Controller\Result\JsonFactory',
            [],
            [],
            '',
            false
        );

        $this->_helperDataMock = $this->getMock(
            '\Doofinder\Feed\Helper\Data',
            [],
            [],
            '',
            false
        );

        $this->_generatorFactoryMock = $this->getMock(
            '\Doofinder\Feed\Model\GeneratorFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_generatorMock = $this->getMock(
            '\Doofinder\Feed\Model\Generator',
            [],
            [],
            '',
            false
        );

        $this->_xmlMock = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            [],
            [],
            '',
            false
        );

        $this->_responseMock = $this->getMock(
            '\Magento\Framework\App\Response\Http',
            [],
            [],
            '',
            false
        );

        $this->_requestMock = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            [],
            [],
            ''
        );

        $this->_feedConfigMock = $this->getMock(
            '\Doofinder\Feed\Helper\FeedConfig',
            [],
            [],
            '',
            false
        );

        $this->_feedConfigMock->expects($this->once())
            ->method('getFeedConfig')
            ->willReturn(array('test' => 'test'));

        $this->_contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->_responseMock);

        $this->_contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->_requestMock);

        $this->_generatorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_generatorMock);

        $this->_generatorMock->expects($this->once())
            ->method('getProcessor')
            ->with('Xml')
            ->willReturn($this->_xmlMock);

        $this->_xmlMock->expects($this->once())
            ->method('getFeed');

        $this->_responseMock->expects($this->once())
            ->method('setBody');

        $this->_controller = $this->_objectManager->getObject(
            '\Doofinder\Feed\Controller\Feed\Index',
            [
                'context'       => $this->_contextMock,
                'jsonFactory'   => $this->_jsonFactoryMock,
                'helperData'    => $this->_helperDataMock,
                'generatorFactory' => $this->_generatorFactoryMock,
                'feedConfig' => $this->_feedConfigMock
            ]
        );
    }

    /**
     * Test execute() with custom feed params method.
     */
    public function testExecuteWithCustomParams()
    {
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->will($this->onConsecutiveCalls(1, 1, '20'));

        $this->_feedConfigMock->expects($this->once())
            ->method('setCustomParams')
            ->willReturnSelf();

        $this->_controller->execute();
    }

    /**
     * Test execute() without custom params.
     */
    public function testExecuteWithoutCustomParams()
    {
        $this->_feedConfigMock->expects($this->never())
            ->method('setCustomParams');

        $this->_requestMock->expects($this->any())
            ->method('getParam');

        $this->_controller->execute();
    }
}