<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\StartTime
 */
class StartTimeTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    private $timezone;

    /**
     * @var integer $offset
     */
    private $offset;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\StartTime
     */
    private $model;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->date = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timezone = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\Timezone::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezone->method('getDefaultTimezone')->willReturn('UTC');
        $this->timezone->method('getConfigTimezone')->willReturn('America/Los_Angeles');
        $this->timezone->method('date')->willReturn($this->date);
        // @codingStandardsIgnoreStart
        $this->offset = (new \DateTimeZone('America/Los_Angeles'))->getOffset(
            new \DateTime(null, new \DateTimeZone('UTC'))
        ) / 3600;
        // @codingStandardsIgnoreEnd

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\StartTime::class,
            [
                'timezone' => $this->timezone,
            ]
        );
    }

    /**
     * Test afterLoad() method
     *
     * @return void
     */
    public function testAfterLoad()
    {
        $this->date->expects($this->once())
            ->method('setTime')->with(10, 30, 0);
        $this->date->method('format')
            ->with('H,i,s')->willReturn('0' . (10 + $this->offset) . ',30,00');

        $this->model->setValue('10,30,00');
        $this->model->afterLoad();

        $this->assertEquals('0' . (10 + $this->offset) . ',30,00', $this->model->getValue());
    }

    /**
     * Test beforeSave() method
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $this->date->expects($this->once())
            ->method('setTime')->with(10, 30, 0);
        $this->date->method('format')
            ->with('H,i,s')->willReturn('0' . (10 - $this->offset) . ',30,00');

        $this->model->setValue(['10', '30', '00']);
        $this->model->beforeSave();

        $this->assertEquals([10 - $this->offset, '30', '00'], $this->model->getValue());
    }
}
