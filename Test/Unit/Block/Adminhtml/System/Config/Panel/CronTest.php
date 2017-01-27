<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config\Panel;

use \Doofinder\Feed\Block\Adminhtml\System\Config\Panel\Cron;

/**
 * Class CronTest
 *
 * @package Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config\Panel
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $_context;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule
     */
    protected $_schedule;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected $_scheduleCollection;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    protected $_scheduleCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_timezone;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\System\Config\Panel\Cron
     */
    protected $_block;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_context = $this->getMock(
            '\Magento\Backend\Block\Template\Context',
            [],
            [],
            '',
            false
        );

        $this->_element = $this->getMock(
            '\Magento\Framework\Data\Form\Element\AbstractElement',
            [],
            [],
            '',
            false
        );
        $this->_element->method('getHtmlId')->willReturn('sample_id');
        $this->_element->method('getElementHtml')->willReturn('sample value');

        $this->_schedule = $this->getMock(
            '\Magento\Cron\Model\Schedule',
            [],
            [],
            '',
            false
        );

        $this->_scheduleCollection = $this->getMock(
            '\Magento\Cron\Model\ResourceModel\Schedule\Collection',
            [],
            [],
            '',
            false
        );
        $this->_scheduleCollection->method('getFirstItem')->willReturn($this->_schedule);

        $this->_scheduleCollectionFactory = $this->getMock(
            '\Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_scheduleCollectionFactory->method('create')->willReturn($this->_scheduleCollection);

        $this->_timezone = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\Timezone',
            [],
            [],
            '',
            false
        );
        $this->_timezone->method('getConfigTimezone')->willReturn('UTC');

        $this->_block = $this->_objectManager->getObject(
            '\Doofinder\Feed\Block\Adminhtml\System\Config\Panel\Cron',
            [
                'context' => $this->_context,
                'scheduleCollectionFactory' => $this->_scheduleCollectionFactory,
                'timezone' => $this->_timezone,
            ]
        );
    }

    /**
     * Test render() method.
     *
     * @dataProvider renderProvider
     */
    public function testRender($date, $expected)
    {
        $date = new \DateTime($date, new \DateTimeZone('UTC'));
        $finishedAt = $date->format('Y-m-d H:i:s');

        $this->_schedule->method('getData')->will($this->returnValueMap([
            ['finished_at', null, $finishedAt],
        ]));

        $this->_scheduleCollection->method('getSize')->willReturn(1);

        $expected = $expected ? __($expected, $date->format('Y-m-d H:i:s')) : $expected;
        $this->_element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"></td><td class="value">sample value</td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }

    public function renderProvider()
    {
        return [
            ['-' . (Cron::ALLOWED_TIME + 1) . ' seconds', Cron::CRON_DELAYED_MSG],
            ['-' . (Cron::ALLOWED_TIME - 1) . ' seconds', ''],
        ];
    }

    /**
     * Test render() method when no cron tasks.
     */
    public function testRenderNoCronTasks()
    {
        $this->_scheduleCollection->method('getSize')->willReturn(0);

        $expected = __(Cron::NO_CRON_TASKS_MSG);
        $this->_element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"></td><td class="value">sample value</td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }

    /**
     * Test render() method when cron task not finished.
     */
    public function testRenderNotFinished()
    {
        $this->_scheduleCollection->method('getSize')->willReturn(1);

        $expected = __(Cron::CRON_NOT_FINISHED_MSG);
        $this->_element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"></td><td class="value">sample value</td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }
}
