<?php

namespace Doofinder\Feed\Test\Unit\Observer\Processor\Xml;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

class ItemsFetchedTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Observer\Processor\Xml\ItemsFetched
     */
    private $_observer;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $_invokedObserver;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $_xmlProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherInterface
     */
    private $_fetcher;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_xmlProcessor = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml',
            ['setStart', 'setEnd'],
            [],
            '',
            false
        );

        $this->_fetcher = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Component\FetcherInterface',
            [],
            [],
            '',
            false
        );

        $this->_generator = $this->getMock(
            '\Doofinder\Feed\Model\Generator',
            ['getProcessor', 'getFetcher'],
            [],
            '',
            false
        );
        $this->_generator->method('getProcessor')->with('Xml')->willReturn($this->_xmlProcessor);

        $this->_invokedObserver = $this->getMock(
            '\Magento\Framework\Event\Observer',
            ['getGenerator'],
            [],
            '',
            false
        );
        $this->_invokedObserver->method('getGenerator')->willReturn($this->_generator);

        $this->_observer = $this->objectManager->getObject(
            '\Doofinder\Feed\Observer\Processor\Xml\ItemsFetched'
        );
    }

    /**
     * Test execute() method
     */
    public function testExecute()
    {
        $this->_generator->method('getFetcher')->willReturn([$this->_fetcher]);
        $this->_fetcher->method('isStarted')->willReturn(false);
        $this->_fetcher->method('isDone')->willReturn(false);

        $this->_xmlProcessor->expects($this->once())
            ->method('setStart')
            ->with(false);

        $this->_xmlProcessor->expects($this->once())
            ->method('setEnd')
            ->with(false);

        $this->_observer->execute($this->_invokedObserver);
    }

    /**
     * Test execute() method with no fetchers
     */
    public function testExecuteNoFetchers()
    {
        $this->_generator->method('getFetcher')->willReturn([]);

        $this->_xmlProcessor->expects($this->once())
            ->method('setStart')
            ->with(true);

        $this->_xmlProcessor->expects($this->once())
            ->method('setEnd')
            ->with(true);

        $this->_observer->execute($this->_invokedObserver);
    }

    /**
     * Test execute() method for multiple Fetchers
     */
    public function testExecuteMultipleFetchers()
    {
        $this->_generator->method('getFetcher')->willReturn([$this->_fetcher, $this->_fetcher]);
        $this->_fetcher->method('isStarted')->will(
            $this->onConsecutiveCalls(true, false)
        );
        $this->_fetcher->method('isDone')->will(
            $this->onConsecutiveCalls(false, true)
        );

        $this->_xmlProcessor->expects($this->once())
            ->method('setStart')
            ->with(false);

        $this->_xmlProcessor->expects($this->once())
            ->method('setEnd')
            ->with(false);

        $this->_observer->execute($this->_invokedObserver);
    }
}
