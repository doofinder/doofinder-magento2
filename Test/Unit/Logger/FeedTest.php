<?php

namespace Doofinder\Feed\Test\Unit\Logger;

use Doofinder\Feed\Test\Unit\BaseTestCase;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test class for \Doofinder\Feed\Logger\Feed
 */
class FeedTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $parentLogger;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Monolog\Handler\AbstractHandler
     */
    private $handler;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->parentLogger = $this->getMock(
            \Magento\Framework\Logger\Monolog::class,
            [],
            [],
            '',
            false
        );

        $this->process = $this->getMock(
            \Doofinder\Feed\Model\Cron::class,
            [],
            [],
            '',
            false
        );

        $this->handler = $this->getMock(
            \Monolog\Handler\AbstractHandler::class,
            [],
            [],
            '',
            false
        );

        $this->logger = $this->objectManager->getObject(
            \Doofinder\Feed\Logger\Feed::class,
            [
                'logger' => $this->parentLogger,
                'process' => $this->process,
                'handlers' => [$this->handler],
            ]
        );
    }

    /**
     * Test addRecord() method
     *
     * @param  array $context
     * @param  boolean $useProcess
     * @param  boolean $callsParent
     * @return void
     * @dataProvider providerTestAddRecord
     */
    public function testAddRecord(array $context, $useProcess, $callsParent)
    {
        $level = 100;
        $message = 'Sample message';

        $expected = $context;
        if ($useProcess) {
            $expected['process'] = $this->process;
        }

        $this->parentLogger->expects($this->once())->method('addRecord')
            ->with($level, $message, $expected);

        if ($callsParent) {
            $this->handler->expects($this->once())->method('isHandling');
        }

        $this->logger->addRecord($level, $message, $context);
    }

    /**
     * Data provider for testAddRecord() test
     *
     * @return array
     */
    public function providerTestAddRecord()
    {
        return [
            [[], true, true],
            [['process' => 'sample'], false, false],
        ];
    }
}
