<?php

namespace Doofinder\Feed\Test\Unit\Controller\Feed;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Controller\Feed\Index
 */
class IndexTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $xml;

    /**
     * @var Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Doofinder\Feed\Controller\Feed\Index
     */
    private $controller;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->helper = $this->getMock(
            \Doofinder\Feed\Helper\Data::class,
            [],
            [],
            '',
            false
        );

        $this->xml = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\Processor\Xml::class,
            [],
            [],
            '',
            false
        );
        $this->xml->expects($this->once())->method('getFeed');

        $this->generator = $this->getMock(
            \Doofinder\Feed\Model\Generator::class,
            [],
            [],
            '',
            false
        );
        $this->generator->expects($this->once())->method('getProcessor')
            ->with('Xml')->willReturn($this->xml);

        $this->generatorFactory = $this->getMock(
            \Doofinder\Feed\Model\GeneratorFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->generatorFactory->expects($this->once())
            ->method('create')->willReturn($this->generator);

        $this->response = $this->getMock(
            \Magento\Framework\App\Response\Http::class,
            [],
            [],
            '',
            false
        );
        $this->response->expects($this->once())->method('setBody');

        $this->request = $this->getMock(
            \Magento\Framework\App\RequestInterface::class,
            [],
            [],
            ''
        );

        $this->feedConfig = $this->getMock(
            \Doofinder\Feed\Helper\FeedConfig::class,
            [],
            [],
            '',
            false
        );
        $this->feedConfig->expects($this->once())
            ->method('getFeedConfig')->willReturn(['test' => 'test']);

        $this->context = $this->getMock(
            \Magento\Framework\App\Action\Context::class,
            [],
            [],
            '',
            false
        );
        $this->context->method('getResponse')->willReturn($this->response);
        $this->context->method('getRequest')->willReturn($this->request);

        $this->controller = $this->objectManager->getObject(
            \Doofinder\Feed\Controller\Feed\Index::class,
            [
                'context' => $this->context,
                'helper' => $this->helper,
                'generatorFactory' => $this->generatorFactory,
                'feedConfig' => $this->feedConfig
            ]
        );
    }

    /**
     * Test execute() method with custom feed params
     *
     * @return void
     */
    public function testExecuteWithCustomParams()
    {
        $this->helper->expects($this->any())
            ->method('getParamString')->willReturn('default');
        $this->helper->expects($this->any())
            ->method('getParamInt')->will($this->onConsecutiveCalls(20, 1));

        $this->feedConfig->expects($this->once())
            ->method('getFeedConfig')
            ->with('default', [
                'limit' => 1,
                'offset' => 20,
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }
}
