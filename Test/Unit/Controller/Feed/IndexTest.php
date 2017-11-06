<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class IndexTest
 * @package Doofinder\Feed\Test\Unit\Controller\Feed
 */
class IndexTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $_context;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $_helper;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $_xml;

    /**
     * @var Magento\Framework\App\ResponseInterface
     */
    private $_response;

    /**
     * @var \Doofinder\Feed\Controller\Feed\Index
     */
    private $_controller;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $_feedConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_helper = $this->getMock(
            '\Doofinder\Feed\Helper\Data',
            [],
            [],
            '',
            false
        );

        $this->_xml = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            [],
            [],
            '',
            false
        );
        $this->_xml->expects($this->once())->method('getFeed');

        $this->_generator = $this->getMock(
            '\Doofinder\Feed\Model\Generator',
            [],
            [],
            '',
            false
        );
        $this->_generator->expects($this->once())->method('getProcessor')
            ->with('Xml')->willReturn($this->_xml);

        $this->_generatorFactory = $this->getMock(
            '\Doofinder\Feed\Model\GeneratorFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_generatorFactory->expects($this->once())
            ->method('create')->willReturn($this->_generator);

        $this->_response = $this->getMock(
            '\Magento\Framework\App\Response\Http',
            [],
            [],
            '',
            false
        );
        $this->_response->expects($this->once())->method('setBody');

        $this->_request = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            [],
            [],
            ''
        );

        $this->_feedConfig = $this->getMock(
            '\Doofinder\Feed\Helper\FeedConfig',
            [],
            [],
            '',
            false
        );
        $this->_feedConfig->expects($this->once())
            ->method('getFeedConfig')->willReturn(['test' => 'test']);

        $this->_context = $this->getMock(
            '\Magento\Framework\App\Action\Context',
            [],
            [],
            '',
            false
        );
        $this->_context->method('getResponse')->willReturn($this->_response);
        $this->_context->method('getRequest')->willReturn($this->_request);

        $this->_controller = $this->objectManager->getObject(
            '\Doofinder\Feed\Controller\Feed\Index',
            [
                'context' => $this->_context,
                'helper' => $this->_helper,
                'generatorFactory' => $this->_generatorFactory,
                'feedConfig' => $this->_feedConfig
            ]
        );
    }

    /**
     * Test execute() with custom feed params method.
     */
    public function testExecuteWithCustomParams()
    {
        $this->_helper->expects($this->any())
            ->method('getParamString')->willReturn('default');
        $this->_helper->expects($this->any())
            ->method('getParamInt')->will($this->onConsecutiveCalls(20, 1));

        $this->_feedConfig->expects($this->once())
            ->method('getFeedConfig')
            ->with('default', [
                'limit' => 1,
                'offset' => 20,
            ])
            ->willReturnSelf();

        $this->_controller->execute();
    }
}
