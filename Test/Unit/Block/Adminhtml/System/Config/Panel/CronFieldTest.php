<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config\Panel;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class CronFieldTest
 *
 * @package Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config\Panel
 */
class CronFieldTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_request;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    private $_context;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $_element;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $_timezone;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\System\Config\Panel\Cron
     */
    private $_block;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_request = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            [],
            [],
            '',
            false
        );
        $this->_request->method('getParam')->with('store')->willReturn(1);

        $this->_store =  $this->getMock(
            '\Magento\Store\Model\Store',
            [],
            [],
            '',
            false
        );
        $this->_store->method('getCode')->willReturn('sample');

        $this->_storeManager =  $this->getMock(
            '\Magento\Store\Model\StoreManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->_storeManager->method('getStore')->with(1)->willReturn($this->_store);

        $this->_context = $this->getMock(
            '\Magento\Backend\Block\Template\Context',
            [],
            [],
            '',
            false
        );
        $this->_context->method('getRequest')->willReturn($this->_request);
        $this->_context->method('getStoreManager')->willReturn($this->_storeManager);

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
            '\Doofinder\Feed\Helper\Schedule',
            [],
            [],
            '',
            false
        );

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
        $this->_timezone->method('getConfigTimezone')->willReturn('UTC');
        $this->_timezone->method('scopeDate')->willReturn($this->_date);
        $this->_timezone->method('formatDateTime')->willReturn('2000-10-05 14:20:00');

        $this->_process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );
        $this->_process->method('getId')->willReturn(5);

        $this->_block = $this->objectManager->getObject(
            '\Doofinder\Feed\Block\Adminhtml\System\Config\Panel\CronField',
            [
                'context' => $this->_context,
                'schedule' => $this->_schedule,
                'timezone' => $this->_timezone,
            ]
        );
    }

    /**
     * Test render() method.
     *
     * @dataProvider renderProvider
     */
    public function testRender($name, $field, $value, $expected)
    {
        $this->_process->method('getData')->with($field)->willReturn($value);

        $this->_schedule->expects($this->once())->method('getProcessByStoreCode')
            ->with('sample')->willReturn($this->_process);

        $this->_element->method('getName')->willReturn($name);
        $this->_element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span>' .
                    '</span></label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }

    public function renderProvider()
    {
        return [
            [
                'groups[some_name][fields][status][value]',
                'status',
                'some status',
                '<span class="feed-message">some status</span>',
            ],
            [
                'groups[some_name][fields][message][value]',
                'message',
                'some message',
                '<span class="feed-message">some message</span>',
            ],
            [
                'groups[some_name][fields][next_run][value]',
                'next_run',
                '2000-10-05 14:20:00',
                '<span class="feed-message">2000-10-05 14:20:00</span>',
            ],
            [
                'groups[some_name][fields][next_iteration][value]',
                'next_iteration',
                '2000-10-05 14:20:00',
                '<span class="feed-message">2000-10-05 14:20:00</span>',
            ],
            [
                'groups[some_name][fields][last_feed_name][value]',
                'last_feed_name',
                'None',
                '<span class="feed-message">Currently there is no file to preview.</span>',
            ],
            [
                'groups[some_name][fields][other][value]',
                'other',
                null,
                '',
            ],
        ];
    }

    /**
     * Test render() method when no process.
     *
     * @dataProvider renderNoProcessProvider
     */
    public function testRenderNoProcess($name, $expected)
    {
        $this->_element->method('getName')->willReturn($name);
        $this->_element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span></span>' .
                    '</label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }

    public function renderNoProcessProvider()
    {
        return [
            [
                'groups[some_name][fields][status][value]',
                '<span class="feed-message">Not created</span>',
            ],
            [
                'groups[some_name][fields][message][value]',
                '<span class="feed-message">Process not created yet, ' .
                'it will be created automatically by cron job</span>',
            ],
            [
                'groups[some_name][fields][other][value]',
                '',
            ],
        ];
    }

    /**
     * Test render() method for last feed name.
     */
    public function testRenderLastFeedName()
    {
        $this->_process->method('getData')->with('last_feed_name')->willReturn('feed-name');

        $this->_schedule->expects($this->once())->method('getProcessByStoreCode')
            ->with('sample')->willReturn($this->_process);

        $this->_schedule->method('isFeedFileExist')->with('sample')->willReturn(true);
        $this->_schedule->method('getFeedFileUrl')->with('sample')->willReturn('http://example/path/to/feed');

        $this->_element->method('getName')->willReturn('groups[some_name][fields][last_feed_name][value]');
        $this->_element->expects($this->once())->method('setData')->with(
            'text',
            '<span class="feed-message"><a href="http://example/path/to/feed target="_blank">Get feed-name</a></span>'
        );

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span></span>' .
                    '</label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }
}
