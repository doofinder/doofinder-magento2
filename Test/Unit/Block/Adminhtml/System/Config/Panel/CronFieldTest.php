<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config\Panel;

/**
 * Test class for \Doofinder\Feed\Block\Adminhtml\System\Config\Panel\Cron
 */
class CronFieldTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $element;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timezone;

    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\System\Config\Panel\Cron
     */
    private $block;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->method('getParam')->with('store')->willReturn(1);

        $this->store =  $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store->method('getCode')->willReturn('sample');

        $this->storeManager =  $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager->method('getStore')->with(1)->willReturn($this->store);

        $this->date = $this->getMockBuilder(\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timezone = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\Timezone::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timezone->method('getConfigTimezone')->willReturn('UTC');
        $this->timezone->method('scopeDate')->willReturn($this->date);
        $this->timezone->method('formatDateTime')->willReturn('2000-10-05 14:20:00');

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getStoreManager')->willReturn($this->storeManager);
        $this->context->method('getLocaleDate')->willReturn($this->timezone);

        $this->element = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->element->method('getHtmlId')->willReturn('sample_id');
        $this->element->method('getElementHtml')->willReturn('sample value');

        $this->schedule = $this->getMockBuilder(\Doofinder\Feed\Helper\Schedule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->process = $this->getMockBuilder(\Doofinder\Feed\Model\Cron::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->process->method('getId')->willReturn(5);

        $this->block = $this->objectManager->getObject(
            \Doofinder\Feed\Block\Adminhtml\System\Config\Panel\CronField::class,
            [
                'context' => $this->context,
                'schedule' => $this->schedule,
            ]
        );
    }

    /**
     * Test render() method
     *
     * @param  string $name
     * @param  string $field
     * @param  string $value
     * @param  string $expected
     * @return void
     * @dataProvider renderProvider
     */
    public function testRender($name, $field, $value, $expected)
    {
        $this->process->method('getData')->with($field)->willReturn($value);

        $this->schedule->expects($this->once())->method('getProcessByStoreCode')
            ->with('sample')->willReturn($this->process);

        $this->element->method('getName')->willReturn($name);
        $this->element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span>' .
                    '</span></label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->block->render($this->element));
    }

    /**
     * Data provider for render() test
     *
     * @return array
     */
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
     * @param  string $name
     * @param  string $expected
     * @return void
     * @dataProvider renderNoProcessProvider
     */
    public function testRenderNoProcess($name, $expected)
    {
        $this->element->method('getName')->willReturn($name);
        $this->element->expects($this->once())->method('setData')->with('text', $expected);

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span></span>' .
                    '</label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->block->render($this->element));
    }

    /**
     * Data provider for render() test when no process
     *
     * @return array
     */
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
     *
     * @return void
     */
    public function testRenderLastFeedName()
    {
        $this->process->method('getData')->with('last_feed_name')->willReturn('feed-name');

        $this->schedule->expects($this->once())->method('getProcessByStoreCode')
            ->with('sample')->willReturn($this->process);

        $this->schedule->method('isFeedFileExist')->with('sample')->willReturn(true);
        $this->schedule->method('getFeedFileUrl')->with('sample')->willReturn('http://example/path/to/feed');

        $this->element->method('getName')->willReturn('groups[some_name][fields][last_feed_name][value]');
        $this->element->expects($this->once())->method('setData')->with(
            'text',
            '<span class="feed-message"><a href="http://example/path/to/feed target="_blank">Get feed-name</a></span>'
        );

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span></span>' .
                    '</label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->block->render($this->element));
    }
}
