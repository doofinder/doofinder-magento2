<?php

namespace Doofinder\Feed\Test\Unit\Logger\Handler;

use Doofinder\Feed\Test\Unit\BaseTestCase;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test class for \Doofinder\Feed\Logger\Feed\Handler
 */
class FeedTest extends BaseTestCase
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

        $this->process = $this->getMock(
            \Doofinder\Feed\Model\Cron::class,
            [],
            [],
            '',
            false
        );
        $this->process->method('getId')->willReturn(15);

        $this->logResource = $this->getMock(
            \Doofinder\Feed\Model\ResourceModel\Log::class,
            [],
            [],
            '',
            false
        );

        $this->logEntry = $this->getMock(
            \Doofinder\Feed\Model\Log::class,
            [],
            [],
            '',
            false
        );
        $this->logEntry->method('getResource')->willReturn($this->logResource);

        $this->logFactory = $this->getMock(
            \Doofinder\Feed\Model\LogFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->logFactory->method('create')->willReturn($this->logEntry);

        $this->datetime = $this->getMock(
            \Magento\Framework\Stdlib\DateTime::class,
            [],
            [],
            '',
            false
        );

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
