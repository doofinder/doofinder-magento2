<?php

namespace Doofinder\Feed\Test\Unit\Logger\Handler;

/**
 * Test class for \Doofinder\Feed\Logger\Feed\Handler
 */
class FeedTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Logger\Feed\Handler
     */
    private $handler;

    /**
     * @var \Doofinder\Feed\Model\Log
     */
    private $logEntry;

    /**
     * @var \Doofinder\Feed\Model\LogFactory
     */
    private $logFactory;

    /**
     * @var \Doofinder\Feed\Model\ResourceModel\Log
     */
    private $logResource;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $datetime;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->process = $this->getMockBuilder(\Doofinder\Feed\Model\Cron::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->process->method('getId')->willReturn(15);

        $this->logResource = $this->getMockBuilder(\Doofinder\Feed\Model\ResourceModel\Log::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logEntry = $this->getMockBuilder(\Doofinder\Feed\Model\Log::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logEntry->method('getResource')->willReturn($this->logResource);

        $this->logFactory = $this->getMockBuilder(\Doofinder\Feed\Model\LogFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->logFactory->method('create')->willReturn($this->logEntry);

        $this->datetime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = $this->objectManager->getObject(
            \Doofinder\Feed\Logger\Handler\Feed::class,
            [
                'logFactory' => $this->logFactory,
                'datetime' => $this->datetime,
            ]
        );
    }

    /**
     * Test write() method
     *
     * @return void
     */
    public function testWrite()
    {
        // @codingStandardsIgnoreStart
        $date = new \DateTime();
        // @codingStandardsIgnoreEnd

        $record = [
            'level' => 100,
            'level_name' => 'DEBUG',
            'message' => 'Sample message',
            'extra' => [],
            'context' => ['process' => $this->process],
            'datetime' => $date,
        ];

        $this->logEntry->expects($this->once())->method('setData')->with([
            'message' => 'Sample message',
            'type' => 'debug',
            'process_id' => 15,
            'time' => '2000-10-10 10:10',
        ]);

        $this->datetime->expects($this->once())->method('formatDate')
            ->with($date)->willReturn('2000-10-10 10:10');

        $this->handler->handle($record);
    }
}
