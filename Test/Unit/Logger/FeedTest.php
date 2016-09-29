<?php

namespace Doofinder\Feed\Test\Unit\Logger;

use Magento\Framework\Exception\NoSuchEntityException;

class FeedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $_logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_parentLogger;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Monolog\Handler\AbstractHandler
     */
    private $_handler;

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

        $this->_parentLogger = $this->getMock(
            '\Magento\Framework\Logger\Monolog',
            [],
            [],
            '',
            false
        );

        $this->_process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );

        $this->_handler = $this->getMock(
            '\Monolog\Handler\AbstractHandler',
            [],
            [],
            '',
            false
        );

        $this->_logger = $this->_objectManager->getObject(
            '\Doofinder\Feed\Logger\Feed',
            [
                'logger' => $this->_parentLogger,
                'process' => $this->_process,
                'handlers' => [$this->_handler],
            ]
        );
    }

    /**
     * Test addRecord() method.
     *
     * @dataProvider addRecordProvider
     */
    public function testAddRecord($context, $useProcess, $callsParent)
    {
        $level = 100;
        $message = 'Sample message';

        $expected = $context;
        if ($useProcess) {
            $expected['process'] = $this->_process;
        }

        $this->_parentLogger->expects($this->once())->method('addRecord')
            ->with($level, $message, $expected);

        if ($callsParent) {
            $this->_handler->expects($this->once())->method('isHandling');
        }

        $this->_logger->addRecord($level, $message, $context);
    }

    public function addRecordProvider()
    {
        return [
            [[], true, true],
            [['process' => 'sample'], false, false],
        ];
    }
}
