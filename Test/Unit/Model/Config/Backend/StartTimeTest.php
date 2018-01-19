<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\StartTime
 */
class StartTimeTest extends BaseTestCase
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
     * @var int $offset
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

        $this->date = $this->getMock(
            \DateTime::class,
            [],
            [],
            '',
            false
        );

        $this->timezone = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\Timezone::class,
            [],
            [],
            '',
            false
        );
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
