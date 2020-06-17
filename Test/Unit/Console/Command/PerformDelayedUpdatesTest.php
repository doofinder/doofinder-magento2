<?php

namespace Doofinder\Feed\Test\Unit\Console\Command;

/**
 * Test class for \Doofinder\Feed\Console\Command\PerformDelayedUpdates
 */
class PerformDelayedUpdatesTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Console\Command\PerformDelayedUpdates
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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $state;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $input;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $commandOutput;

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

        $this->state = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->input = $this->getMockBuilder(\Symfony\Component\Console\Input\InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commandOutput = $this->getMockBuilder(\Symfony\Component\Console\Output\OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testedClass = $this->objectManager->getObject(
            \Doofinder\Feed\Console\Command\PerformDelayedUpdates::class,
            [
                'helper' => $this->helper,
                'processor' => $this->processor,
                'state' => $this->state
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteEnabled()
    {
        $this->input->expects($this->once())
            ->method('getOption')
            ->with($this->testedClass::ARG_FORCE)
            ->willReturn(0);

        $this->helper->expects($this->once())
            ->method('isDelayedUpdatesEnabled')
            ->willReturn(true);

        $processor = $this->processor;
        $this->state->expects($this->once())
            ->method('emulateAreaCode')
            ->with(\Magento\Framework\App\Area::AREA_FRONTEND, function () use ($processor) {
                $processor->expects($this->once())
                    ->method('execute');
            });

        $this->commandOutput->expects($this->atLeastOnce())->method('writeln');

        $this->testedClass->execute($this->input, $this->commandOutput);
    }

    /**
     * @return void
     */
    public function testExecuteDisabled()
    {
        $this->input->expects($this->once())
            ->method('getOption')
            ->with($this->testedClass::ARG_FORCE)
            ->willReturn(0);

        $this->helper->expects($this->once())
            ->method('isDelayedUpdatesEnabled')
            ->willReturn(false);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->state->expects($this->never())
            ->method('emulateAreaCode');
        $this->processor->expects($this->never())
            ->method('execute');

        $this->commandOutput->expects($this->never())->method('writeln');

        $this->testedClass->execute($this->input, $this->commandOutput);
    }

    /**
     * @return void
     */
    public function testExecuteDisabledAndForce()
    {
        $this->input->expects($this->once())
            ->method('getOption')
            ->with($this->testedClass::ARG_FORCE)
            ->willReturn(1);

        $this->helper->expects($this->once())
            ->method('isDelayedUpdatesEnabled')
            ->willReturn(false);

        $processor = $this->processor;
        $this->state->expects($this->once())
            ->method('emulateAreaCode')
            ->with(\Magento\Framework\App\Area::AREA_FRONTEND, function () use ($processor) {
                $processor->expects($this->once())
                    ->method('execute');
            });

        $this->commandOutput->expects($this->atLeastOnce())->method('writeln');

        $this->testedClass->execute($this->input, $this->commandOutput);
    }
}
