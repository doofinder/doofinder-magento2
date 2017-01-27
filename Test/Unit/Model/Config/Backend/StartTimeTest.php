<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

/**
 * Class StartTimeTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class StartTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    protected $_timezone;

    /**
     * @var int $offset
     */
    protected $_offset;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\StartTime
     */
    protected $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_timezone = $this->getMock(

            '\Magento\Framework\Stdlib\DateTime\Timezone',
            [],
            [],
            '',
            false
        );
        $this->_timezone->method('getDefaultTimezone')->willReturn('UTC');
        $this->_timezone->method('getConfigTimezone')->willReturn('America/Los_Angeles');
        $this->_offset = (new \DateTimeZone('America/Los_Angeles'))->getOffset(
            new \DateTime(null, new \DateTimeZone('UTC'))
        ) / 3600;

        $this->_model = $this->_objectManager->getObject(
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
        $this->_model->setValue('10,30,00');
        $this->_model->afterLoad();

        $this->assertEquals('0' . (10 + $this->_offset) . ',30,00', $this->_model->getValue());
    }

    /**
     * Test beforeSave()
     */
    public function testBeforeSave()
    {
        $this->_model->setValue(['10', '30', '00']);
        $this->_model->beforeSave();

        $this->assertEquals([10 - $this->_offset, '30', '00'], $this->_model->getValue());
    }
}
