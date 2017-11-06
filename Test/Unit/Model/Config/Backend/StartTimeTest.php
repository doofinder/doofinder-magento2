<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class StartTimeTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class StartTimeTest extends BaseTestCase
{
    /**
     * @var \DateTime
     */
    private $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    private $_timezone;

    /**
     * @var int $offset
     */
    private $_offset;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\StartTime
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_date = $this->getMock(
            '\DateTime',
            [],
            [],
            '',
            false
        );

        $this->_timezone = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\Timezone',
            [],
            [],
            '',
            false
        );
        $this->_timezone->method('getDefaultTimezone')->willReturn('UTC');
        $this->_timezone->method('getConfigTimezone')->willReturn('America/Los_Angeles');
        $this->_timezone->method('date')->willReturn($this->_date);
        // @codingStandardsIgnoreStart
        $this->_offset = (new \DateTimeZone('America/Los_Angeles'))->getOffset(
            new \DateTime(null, new \DateTimeZone('UTC'))
        ) / 3600;
        // @codingStandardsIgnoreEnd

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\StartTime',
            [
                'timezone' => $this->_timezone,
            ]
        );
    }

    /**
     * Test afterLoad()
     */
    public function testAfterLoad()
    {
        $this->_date->expects($this->once())
            ->method('setTime')->with(10, 30, 0);
        $this->_date->method('format')
            ->with('H,i,s')->willReturn('0' . (10 + $this->_offset) . ',30,00');

        $this->_model->setValue('10,30,00');
        $this->_model->afterLoad();

        $this->assertEquals('0' . (10 + $this->_offset) . ',30,00', $this->_model->getValue());
    }

    /**
     * Test beforeSave()
     */
    public function testBeforeSave()
    {
        $this->_date->expects($this->once())
            ->method('setTime')->with(10, 30, 0);
        $this->_date->method('format')
            ->with('H,i,s')->willReturn('0' . (10 - $this->_offset) . ',30,00');

        $this->_model->setValue(['10', '30', '00']);
        $this->_model->beforeSave();

        $this->assertEquals([10 - $this->_offset, '30', '00'], $this->_model->getValue());
    }
}
