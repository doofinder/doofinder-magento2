<?php

namespace Doofinder\Feed\Test\Unit\Cron;

/**
 * Test class for \Doofinder\Feed\Cron\PerformDelayedUpdates
 */
class PerformDelayedUpdatesTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Cron\PerformDelayedUpdates
     */
    private $testedClass;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $processor;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->helper = $this->getMockBuilder(\Doofinder\Feed\Helper\Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = $this->getMockBuilder(\Doofinder\Feed\Model\ChangedProduct\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testedClass = $this->objectManager->getObject(
            \Doofinder\Feed\Cron\PerformDelayedUpdates::class,
            [
                'helper' => $this->helper,
                'processor' => $this->processor,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteEnabled()
    {
        $this->helper->expects($this->once())
            ->method('isDelayedUpdatesEnabled')
            ->willReturn(true);

        $this->processor->expects($this->once())
            ->method('execute');
        $this->testedClass->execute();
    }

    /**
     * @return void
     */
    public function testExecuteDisabled()
    {

        $this->helper->expects($this->once())
            ->method('isDelayedUpdatesEnabled')
            ->willReturn(false);

        $this->processor->expects($this->never())
            ->method('execute');

        $this->testedClass->execute();
    }
}
