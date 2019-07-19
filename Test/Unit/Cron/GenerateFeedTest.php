<?php

namespace Doofinder\Feed\Test\Unit\Cron;

/**
 * Test class for \Doofinder\Feed\Cron\GenerateFeed
 */
class GenerateFeedTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Doofinder\Feed\Cron\GenerateFeed
     */
    private $cron;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->schedule = $this->getMockBuilder(\Doofinder\Feed\Helper\Schedule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->process = $this->getMockBuilder(\Doofinder\Feed\Model\Cron::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cron = $this->objectManager->getObject(
            \Doofinder\Feed\Cron\GenerateFeed::class,
            [
                'schedule'  => $this->schedule,
            ]
        );
    }

    /**
     * Test execute() method
     *
     * @return void
     */
    public function testExecute()
    {
        $this->schedule->expects($this->once())->method('getActiveProcess')->willReturn($this->process);
        $this->schedule->expects($this->once())->method('runProcess')->with($this->process);

        $this->cron->execute();
    }
}
