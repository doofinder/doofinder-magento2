<?php

namespace Doofinder\Feed\Test\Unit\Observer\Processor\Xml;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Observer\Processor\Xml\ItemsFetched
 */
class ItemsFetchedTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Observer\Processor\Xml\ItemsFetched
     */
    private $observer;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    private $invokedObserver;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $generator;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $xmlProcessor;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\FetcherInterface
     */
    private $fetcher;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->xmlProcessor = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\Processor\Xml::class,
            ['setStart', 'setEnd'],
            [],
            '',
            false
        );

        $this->fetcher = $this->getMock(
            \Doofinder\Feed\Model\Generator\Component\FetcherInterface::class,
            [],
            [],
            '',
            false
        );

        $this->generator = $this->getMock(
            \Doofinder\Feed\Model\Generator::class,
            ['getProcessor', 'getFetcher'],
            [],
            '',
            false
        );
        $this->generator->method('getProcessor')->with('Xml')->willReturn($this->xmlProcessor);

        $this->invokedObserver = $this->getMock(
            \Magento\Framework\Event\Observer::class,
            ['getGenerator'],
            [],
            '',
            false
        );
        $this->invokedObserver->method('getGenerator')->willReturn($this->generator);

        $this->observer = $this->objectManager->getObject(
            \Doofinder\Feed\Observer\Processor\Xml\ItemsFetched::class
        );
    }

    /**
     * Test execute() method
     *
     * @return void
     */
    public function testExecute()
    {
        $this->generator->method('getFetcher')->willReturn([$this->fetcher]);
        $this->fetcher->method('isStarted')->willReturn(false);
        $this->fetcher->method('isDone')->willReturn(false);

        $this->xmlProcessor->expects($this->once())
            ->method('setStart')
            ->with(false);

        $this->xmlProcessor->expects($this->once())
            ->method('setEnd')
            ->with(false);

        $this->observer->execute($this->invokedObserver);
    }

    /**
     * Test execute() method with no fetchers
     *
     * @return void
     */
    public function testExecuteNoFetchers()
    {
        $this->generator->method('getFetcher')->willReturn([]);

        $this->xmlProcessor->expects($this->once())
            ->method('setStart')
            ->with(true);

        $this->xmlProcessor->expects($this->once())
            ->method('setEnd')
            ->with(true);

        $this->observer->execute($this->invokedObserver);
    }

    /**
     * Test execute() method for multiple fetchers
     *
     * @return void
     */
    public function testExecuteMultipleFetchers()
    {
        $this->generator->method('getFetcher')->willReturn([$this->fetcher, $this->fetcher]);
        $this->fetcher->method('isStarted')->will(
            $this->onConsecutiveCalls(true, false)
        );
        $this->fetcher->method('isDone')->will(
            $this->onConsecutiveCalls(false, true)
        );

        $this->xmlProcessor->expects($this->once())
            ->method('setStart')
            ->with(false);

        $this->xmlProcessor->expects($this->once())
            ->method('setEnd')
            ->with(false);

        $this->observer->execute($this->invokedObserver);
    }
}
