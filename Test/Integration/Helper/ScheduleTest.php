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
    }

    /**
     * Test for getStoreConfig() method.
     */
    public function testGetStoreConfig()
    {
        $this->assertEquals(
            [
                'store_code' => 'default',
                'enabled' => '1',
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
    public function testTimeArrayToDate($time, $timezone, $base, $expected)
    {
        $date = $this->_helper->timeArrayToDate($time, $timezone, $base);

        $this->assertEquals($expected, $date);
    }

    public function testTimeArrayToDateProvider()
    {
        return [
            [
                [10, 0, 0],
                'Europe/Berlin',
                new \DateTime('2016-01-01 00:00:00'),
                new \DateTime('2016-01-01 10:00:00', new \DateTimeZone('Europe/Berlin'))
            ],
            [
                [20, 0, 0],
                'Europe/Berlin',
                new \DateTime('2016-05-01 00:00:00'),
                new \DateTime('2016-05-01 20:00:00', new \DateTimeZone('Europe/Berlin'))
            ],
            [
                [10, 0, 0],
                null,
                new \DateTime('2017-02-11 00:00:00'),
                new \DateTime('2017-02-11 10:00:00')
            ],
            [
                [0, 0, 0],
                null,
                new \DateTime('2016-01-01 00:00:00'),
                new \DateTime('2016-01-01 00:00:00')
            ],
            [
                [4, 30, 0],
                null,
                new \DateTime('2016-01-01 03:00:00', new \DateTimeZone('Europe/Berlin')),
                new \DateTime('2016-01-01 04:30:00')
            ],
        ];
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
    }

    /**
     * Test regenerateSchedule() method
     *
     * @magentoDbIsolation enabled
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
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+1 day')->setTime(0, 0, 0),
            new \DateTime($process->getNextRun(), $this->getDefaultTimezone())
        );
        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+1 day')->setTime(0, 0, 0),
            new \DateTime($process->getNextIteration(), $this->getDefaultTimezone())
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
     */
    public function testUpdateProcessWeekly()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+7 days')->setTime(0, 0, 0),
            new \DateTime($process->getNextRun(), $this->getDefaultTimezone())
        );
        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+7 days')->setTime(0, 0, 0),
            new \DateTime($process->getNextIteration(), $this->getDefaultTimezone())
        );
    }

    /**
     * Test updateProcess() method monthly
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/frequency M
     */
    public function testUpdateProcessMonthly()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+1 month')->setTime(0, 0, 0),
            new \DateTime($process->getNextRun(), $this->getDefaultTimezone())
        );
        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+1 month')->setTime(0, 0, 0),
            new \DateTime($process->getNextIteration(), $this->getDefaultTimezone())
        );
    }

    /**
     * Test updateProcess() method custom time
     *
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_feed_feed/feed_cron/start_time 10,15,30
     */
    public function testUpdateProcessCustomTime()
    {
        $process = $this->_helper->updateProcess($this->_defaultStore);

        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))
                ->setTime(10, 15, 30)
                ->format('H:i:s'),
            (new \DateTime($process->getNextRun(), $this->getDefaultTimezone()))
                ->setTimezone($this->getDefaultTimezone())
                ->format('H:i:s')
        );
        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))
                ->setTime(10, 15, 30)
                ->format('H:i:s'),
            (new \DateTime($process->getNextIteration(), $this->getDefaultTimezone()))
                ->setTimezone($this->getDefaultTimezone())
                ->format('H:i:s')
        );
    }

    /**
     * Test getProcessByStoreCode() method
     *
     * @magentoDataFixture process_success
     */
    public function testGetProcessByStoreCode()
    {
        $process = $this->_helper->getProcessByStoreCode($this->_defaultStore->getCode());

        $this->assertEquals($this->_defaultStore->getCode(), $process->getStoreCode());
    }

    /**
     * Test updateProcess() method rescheduling
     *
     * @magentoDataFixture process_success
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
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+1 day')->setTime(0, 0, 0),
            new \DateTime($process->getNextRun(), $this->getDefaultTimezone())
        );
        $this->assertEquals(
            (new \DateTime(null, $this->getDefaultTimezone()))->modify('+1 day')->setTime(0, 0, 0),
            new \DateTime($process->getNextIteration(), $this->getDefaultTimezone())
        );
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextRun());
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextIteration());
        $this->assertEquals('doofinder-default.xml', $process->getLastFeedName());
        $this->assertEquals(0, $process->getOffset());
        $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $process->getCreatedAt());
        $this->assertNotEquals('0000-00-00 00:00:00', $process->getCreatedAt());
    }

    public static function process_success()
    {
        require __DIR__ . '/../_files/process_success.php';
    }

    /**
     * Test getActiveProcess() method
     *
     * @magentoDbIsolation enabled
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
    protected function getDefaultTimezone()
    {
        $timezone = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Stdlib\DateTime\Timezone'
        );

        return new \DateTimeZone($timezone->getDefaultTimezone());
    }

    /**
     * Get config timezone
     *
     * @return \DateTimeZone
     */
    protected function getConfigTimezone()
    {
        $timezone = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Stdlib\DateTime\Timezone'
        );

        return new \DateTimeZone($timezone->getConfigTimezone());
    }
}
