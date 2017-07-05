<?php

namespace Doofinder\Feed\Test\Integration\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\Schedule
 */
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_defaultStore;

    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    private $_cronFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $_timezone;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_helper = $this->_objectManager->create(
            '\Doofinder\Feed\Helper\Schedule'
        );

        $storeManager = $this->_objectManager->get(
            '\Magento\Store\Model\StoreManagerInterface'
        );

        $this->_defaultStore = $storeManager->getStore('default');

        $this->_cronFactory = $this->_objectManager->create(
            '\Doofinder\Feed\Model\CronFactory'
        );

        $this->_timezone = $this->_objectManager->create(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        );
    }

    /**
     * Test for getStoreConfig() method.
     */
    public function testGetStoreConfig()
    {
        $this->assertEquals(
            [
                'store_code' => 'default',
                'enabled' => '0',
                'start_time' => ['0', '0', '0'],
                'frequency' => 'D',
                'step_size' => '1000',
                'step_delay' => '5',
                'image_size' => null,
                'split_configurable_products' => '0',
                'export_product_prices' => '1',
                'price_tax_mode' => 0,
                'attributes' => [
                    'id' => 'df_id',
                    'title' => 'name',
                    'description' => 'short_description',
                    'brand' => 'manufacturer',
                    'link' => 'url_key',
                    'image_link' => 'image',
                    'price' => 'df_regular_price',
                    'sale_price' => 'df_sale_price',
                    'mpn' => 'sku',
                    'availability' => 'df_availability',
                    'categories' => 'category_ids',
                ],
                'atomic_updates_enabled' => 0,
                'categories_in_navigation' => 0,
                'password' => null,
            ],
            $this->_helper->getStoreConfig()
        );
    }

    /**
     * Test for timeArrayToDate() method.
     *
     * @dataProvider testTimeArrayToDateProvider
     */
    public function testTimeArrayToDate($time, $useTimezone, $base, $expected)
    {
        $date = $this->_helper->timeArrayToDate($time, $useTimezone, $base);

        $this->assertEquals($expected, $date);
    }

    public function testTimeArrayToDateProvider()
    {
        $configTimezone = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->getConfigTimezone();

        // @codingStandardsIgnoreStart
        return [
            [
                [10, 0, 0],
                true,
                new \DateTime('2016-01-01 00:00:00', new \DateTimeZone($configTimezone)),
                new \DateTime('2016-01-01 10:00:00', new \DateTimeZone($configTimezone))
            ],
            [
                [10, 0, 0],
                null,
                new \DateTime('2017-02-11 00:00:00'),
                new \DateTime('2017-02-11 10:00:00')
            ],
            [
                [4, 30, 15],
                null,
                new \DateTime('2016-01-01 03:00:00'),
                new \DateTime('2016-01-01 04:30:15')
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test for getScheduleDate() method.
     *
     * @dataProvider testGetScheduleDateProvider
     */
    public function testGetScheduleDate($date, $frequency, $now, $expected)
    {
        $date = $this->_helper->getScheduleDate($date, $frequency, $now);

        $this->assertEquals($expected, $date);
    }

    public function testGetScheduleDateProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                new \DateTime('2016-01-01 12:00:00'),
                \Magento\Cron\Model\Config\Source\Frequency::CRON_DAILY,
                new \DateTime('2016-01-01 06:00:00'),
                new \DateTime('2016-01-01 12:00:00'),
            ],
            [
                new \DateTime('2016-01-01 06:00:00', new \DateTimeZone('Europe/Berlin')),
                \Magento\Cron\Model\Config\Source\Frequency::CRON_DAILY,
                new \DateTime('2016-01-01 12:00:00'),
                new \DateTime('2016-01-02 06:00:00', new \DateTimeZone('Europe/Berlin')),
            ],
            [
                new \DateTime('2016-12-28 20:00:00'),
                \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY,
                new \DateTime('2016-12-28 10:00:00'),
                new \DateTime('2016-12-28 20:00:00'),
            ],
            [
                new \DateTime('2016-12-28 10:00:00'),
                \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY,
                new \DateTime('2016-12-28 20:00:00'),
                new \DateTime('2017-01-04 10:00:00'),
            ],
            [
                new \DateTime('2016-03-06 12:00:00'),
                \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY,
                new \DateTime('2016-03-06 06:00:00'),
                new \DateTime('2016-03-06 12:00:00'),
            ],
            [
                new \DateTime('2016-03-06 06:00:00'),
                \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY,
                new \DateTime('2016-03-06 12:00:00'),
                new \DateTime('2016-04-06 06:00:00'),
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test regenerateSchedule() method
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testRegenerateSchedule()
    {
        $this->_helper->regenerateSchedule();

        $process = $this->_cronFactory->create()->load('default', 'store_code');
        $this->assertEquals('default', $process->getStoreCode());
        $this->assertEquals('pending', $process->getStatus());
    }

    /**
     * Test updateProcess() method
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testUpdateProcess()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals('default', $process->getStoreCode());
        $this->assertEquals('pending', $process->getStatus());
        $this->assertEquals(
            "The new process has been registered and it's waiting to be activated.",
            $process->getMessage()
        );
        $this->assertEquals(0, $process->getErrorStack());
        $this->assertEquals('0%', $process->getComplete());
        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextRun(), null, false)
        );
        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextIteration(), null, false)
        );
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextRun());
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextIteration());
        $this->assertEquals('None', $process->getLastFeedName());
        $this->assertEquals(0, $process->getOffset());
        $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $process->getCreatedAt());
        $this->assertNotEquals('0000-00-00 00:00:00', $process->getCreatedAt());
    }

    /**
     * Test updateProcess() method weekly
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/frequency W
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testUpdateProcessWeekly()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+7 days')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextRun(), null, false)
        );
        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+7 days')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextIteration(), null, false)
        );
    }

    /**
     * Test updateProcess() method monthly
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/frequency M
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testUpdateProcessMonthly()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+1 month')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextRun(), null, false)
        );
        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+1 month')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextIteration(), null, false)
        );
    }

    /**
     * Test updateProcess() method custom time
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/start_time 10,15,30
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testUpdateProcessCustomTime()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals(
            $this->_timezone->date(null, null, false)
                ->setTime(10, 15, 30)
                ->format('H:i:s'),
            $this->_timezone->date($process->getNextRun(), null, false)
                ->setTimezone($this->getDefaultTimezone())
                ->format('H:i:s')
        );
        $this->assertEquals(
            $this->_timezone->date(null, null, false)
                ->setTime(10, 15, 30)
                ->format('H:i:s'),
            $this->_timezone->date($process->getNextIteration(), null, false)
                ->setTimezone($this->getDefaultTimezone())
                ->format('H:i:s')
        );
    }

    /**
     * Test getProcessByStoreCode() method
     *
     * @magentoDataFixture processSuccess
     */
    public function testGetProcessByStoreCode()
    {
        $process = $this->_helper->getProcessByStoreCode($this->_defaultStore->getCode());

        $this->assertEquals($this->_defaultStore->getCode(), $process->getStoreCode());
    }

    /**
     * Test updateProcess() method rescheduling
     *
     * @magentoDataFixture processSuccess
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testUpdateProcessReschedule()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals('default', $process->getStoreCode());
        $this->assertEquals('pending', $process->getStatus());
        $this->assertEquals(
            "The new process has been registered and it's waiting to be activated.",
            $process->getMessage()
        );
        $this->assertEquals(0, $process->getErrorStack());
        $this->assertEquals('0%', $process->getComplete());
        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextRun(), null, false)
        );
        $this->assertEquals(
            $this->_timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->_timezone->date($process->getNextIteration(), null, false)
        );
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextRun());
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextIteration());
        $this->assertEquals('doofinder-default.xml', $process->getLastFeedName());
        $this->assertEquals(0, $process->getOffset());
        $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $process->getCreatedAt());
        $this->assertNotEquals('0000-00-00 00:00:00', $process->getCreatedAt());
    }

    public static function processSuccess()
    {
        // @codingStandardsIgnoreStart
        require __DIR__ . '/../_files/process_success.php';
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test getActiveProcess() method
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/enabled 1
     */
    public function testGetActiveProcess()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore, true, true);

        $this->assertEquals(
            $process->getId(),
            $this->_helper->getActiveProcess()->getId()
        );
    }

    /**
     * Get default timezone
     *
     * @return \DateTimeZone
     */
    private function getDefaultTimezone()
    {
        // @codingStandardsIgnoreStart
        return new \DateTimeZone($this->_timezone->getDefaultTimezone());
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get config timezone
     *
     * @return \DateTimeZone
     */
    private function getConfigTimezone()
    {
        // @codingStandardsIgnoreStart
        return new \DateTimeZone($this->_timezone->getConfigTimezone());
        // @codingStandardsIgnoreEnd
    }
}
