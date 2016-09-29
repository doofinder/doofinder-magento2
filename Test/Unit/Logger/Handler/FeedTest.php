<?php

namespace Doofinder\Feed\Test\Unit\Logger\Handler;

use Magento\Framework\Exception\NoSuchEntityException;

class FeedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Logger\Feed\Handler
     */
    private $_handler;

    /**
     * @var \Doofinder\Feed\Model\Log
     */
    private $_logEntry;

    /**
     * @var \Doofinder\Feed\Model\LogFactory
     */
    private $_logFactory;

    /**
     * @var \Doofinder\Feed\Model\ResourceModel\Log
     */
    private $_logResource;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $_datetime;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $_objectManager;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );
        $this->_process->method('getId')->willReturn(15);

        $this->_logResource = $this->getMock(
            '\Doofinder\Feed\Model\ResourceModel\Log',
            [],
            [],
            '',
            false
        );

        $this->_logEntry = $this->getMock(
            '\Doofinder\Feed\Model\Log',
            [],
            [],
            '',
            false
        );
        $this->_logEntry->method('getResource')->willReturn($this->_logResource);

        $this->_logFactory = $this->getMock(
            '\Doofinder\Feed\Model\LogFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_logFactory->method('create')->willReturn($this->_logEntry);

        $this->_datetime = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime',
            [],
            [],
            '',
            false
        );

        $this->_handler = $this->_objectManager->getObject(
            '\Doofinder\Feed\Logger\Handler\Feed',
            [
                'logFactory' => $this->_logFactory,
                'datetime' => $this->_datetime,
            ]
        );
    }

    /**
     * Test write() method.
     */
    public function testWrite()
    {
        $date = new \DateTime();

        $record = [
            'level' => 100,
            'level_name' => 'DEBUG',
            'message' => 'Sample message',
            'extra' => [],
            'context' => ['process' => $this->_process],
            'datetime' => $date,
        ];

        $this->_logEntry->expects($this->once())->method('setData')->with([
            'message' => 'Sample message',
            'type' => 'debug',
            'process_id' => 15,
            'time' => '2000-10-10 10:10',
        ]);

        $this->_datetime->expects($this->once())->method('formatDate')
            ->with($date)->willReturn('2000-10-10 10:10');

        $this->_handler->handle($record);
    }
}
