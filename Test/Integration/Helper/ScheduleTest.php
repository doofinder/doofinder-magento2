<?php

namespace Doofinder\Feed\Test\Integration\Helper;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Helper\Schedule
 */
class ScheduleTest extends AbstractIntegrity
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $defaultStore;

    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    private $cronFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->helper = $this->objectManager->create(
            \Doofinder\Feed\Helper\Schedule::class
        );

        $storeManager = $this->objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );

        $this->defaultStore = $storeManager->getStore('default');

        $this->cronFactory = $this->objectManager->create(
            \Doofinder\Feed\Model\CronFactory::class
        );

        $this->timezone = $this->objectManager->create(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        );
    }

    /**
     * Test for getStoreConfig() method
     *
     * @return void
     */
    public function testGetStoreConfig()
    {
        $this->assertEquals(
            [
                'store_code' => 'default',
                'enabled' => '0',
                'start_time' => ['0', '0', '0'],
                'step_size' => '1000',
                'step_delay' => '5',
                'image_size' => null,
                'split_configurable_products' => '0',
                'export_product_prices' => '1',
                'price_tax_mode' => '0',
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
                'atomic_updates_enabled' => false,
                'categories_in_navigation' => '0',
                'password' => null,
            ],
            $this->helper->getStoreConfig()
        );
    }

    /**
     * Test for timeArrayToDate() method
     *
     * @param  array $time
     * @param  boolean $useTimezone
     * @param  \DateTime $base
     * @param  \DateTime $expected
     * @return void
     * @dataProvider providerTestTimeArrayToDate
     */
    public function testTimeArrayToDate(array $time, $useTimezone, \DateTime $base, \DateTime $expected)
    {
        $date = $this->helper->timeArrayToDate($time, $useTimezone, $base);

        $this->assertEquals($expected, $date);
    }

    /**
     * Data provider for testTimeArrayToDate() test
     *
     * @return array
     */
    public function providerTestTimeArrayToDate()
    {
        $configTimezone = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
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
     * Test for getScheduleDate() method
     *
     * @param \DateTime $date
     * @param \DateTime $now
     * @param \DateTime $expected
     * @return void
     * @dataProvider providerTestGetScheduleDate
     */
    public function testGetScheduleDate(\DateTime $date, \DateTime $now, \DateTime $expected)
    {
        $date = $this->helper->getScheduleDate($date, $now);

        $this->assertEquals($expected, $date);
    }

    /**
     * Data provider for testGetScheduleDate() test
     *
     * @return array
     */
    public function providerTestGetScheduleDate()
    {
        // @codingStandardsIgnoreStart
        return [
            [
                new \DateTime('2016-01-01 12:00:00'),
                new \DateTime('2016-01-01 06:00:00'),
                new \DateTime('2016-01-01 12:00:00'),
            ],
            [
                new \DateTime('2016-01-01 06:00:00', new \DateTimeZone('Europe/Berlin')),
                new \DateTime('2016-01-01 12:00:00'),
                new \DateTime('2016-01-02 06:00:00', new \DateTimeZone('Europe/Berlin')),
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test regenerateSchedule() method
     *
     * @return void
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_config_data_feed/cron_settings/enabled 1
     */
    public function testRegenerateSchedule()
    {
        $this->helper->regenerateSchedule();

        $process = $this->cronFactory->create()->load('default', 'store_code');
        $this->assertEquals('default', $process->getStoreCode());
        $this->assertEquals('pending', $process->getStatus());
    }

    /**
     * Test updateProcess() method
     *
     * @return void
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_config_data_feed/cron_settings/enabled 1
     */
    public function testUpdateProcess()
    {
        $process = $this->helper->updateProcess($this->defaultStore);

        $this->assertEquals('default', $process->getStoreCode());
        $this->assertEquals('pending', $process->getStatus());
        $this->assertEquals(
            "The new process has been registered and it's waiting to be activated.",
            $process->getMessage()
        );
        $this->assertEquals(0, $process->getErrorStack());
        $this->assertEquals('0%', $process->getComplete());
        $this->assertEquals(
            $this->timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->timezone->date($process->getNextRun(), null, false)
        );
        $this->assertEquals(
            $this->timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->timezone->date($process->getNextIteration(), null, false)
        );
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextRun());
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextIteration());
        $this->assertEquals('None', $process->getLastFeedName());
        $this->assertEquals(0, $process->getOffset());
        $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $process->getCreatedAt());
        $this->assertNotEquals('0000-00-00 00:00:00', $process->getCreatedAt());
    }

    /**
     * Test updateProcess() method custom time
     *
     * @return void
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_config_data_feed/cron_settings/start_time 10,15,30
     * @magentoConfigFixture default_store doofinder_config_data_feed/cron_settings/enabled 1
     */
    public function testUpdateProcessCustomTime()
    {
        $process = $this->helper->updateProcess($this->defaultStore);

        $this->assertEquals(
            $this->timezone->date(null, null, false)
                ->setTime(10, 15, 30)
                ->format('H:i:s'),
            $this->timezone->date($process->getNextRun(), null, false)
                ->setTimezone($this->getDefaultTimezone())
                ->format('H:i:s')
        );
        $this->assertEquals(
            $this->timezone->date(null, null, false)
                ->setTime(10, 15, 30)
                ->format('H:i:s'),
            $this->timezone->date($process->getNextIteration(), null, false)
                ->setTimezone($this->getDefaultTimezone())
                ->format('H:i:s')
        );
    }

    /**
     * Test getProcessByStoreCode() method
     *
     * @return void
     * @magentoDataFixture processSuccess
     */
    public function testGetProcessByStoreCode()
    {
        $process = $this->helper->getProcessByStoreCode($this->defaultStore->getCode());

        $this->assertEquals($this->defaultStore->getCode(), $process->getStoreCode());
    }

    /**
     * Test updateProcess() method rescheduling
     *
     * @return void
     * @magentoDataFixture processSuccess
     * @magentoConfigFixture default_store doofinder_config_data_feed/cron_settings/enabled 1
     */
    public function testUpdateProcessReschedule()
    {
        $process = $this->helper->updateProcess($this->defaultStore);

        $this->assertEquals('default', $process->getStoreCode());
        $this->assertEquals('pending', $process->getStatus());
        $this->assertEquals(
            "The new process has been registered and it's waiting to be activated.",
            $process->getMessage()
        );
        $this->assertEquals(0, $process->getErrorStack());
        $this->assertEquals('0%', $process->getComplete());
        $this->assertEquals(
            $this->timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->timezone->date($process->getNextRun(), null, false)
        );
        $this->assertEquals(
            $this->timezone->date(null, null, false)->modify('+1 day')->setTime(0, 0, 0),
            $this->timezone->date($process->getNextIteration(), null, false)
        );
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextRun());
        $this->assertStringMatchesFormat('%d-%d-%d 00:00:00', $process->getNextIteration());
        $this->assertEquals('doofinder-default.xml', $process->getLastFeedName());
        $this->assertEquals(0, $process->getOffset());
        $this->assertStringMatchesFormat('%d-%d-%d %d:%d:%d', $process->getCreatedAt());
        $this->assertNotEquals('0000-00-00 00:00:00', $process->getCreatedAt());
    }

    /**
     * Fixture with successful process
     *
     * @return void
     */
    public static function processSuccess()
    {
        // @codingStandardsIgnoreStart
        require __DIR__ . '/../_files/process_success.php';
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test getActiveProcess() method
     *
     * @return void
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store doofinder_config_data_feed/cron_settings/enabled 1
     */
    public function testGetActiveProcess()
    {
        $process = $this->helper->updateProcess($this->defaultStore, true, true);

        $this->assertEquals(
            $process->getId(),
            $this->helper->getActiveProcess()->getId()
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
        return new \DateTimeZone($this->timezone->getDefaultTimezone());
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
        return new \DateTimeZone($this->timezone->getConfigTimezone());
        // @codingStandardsIgnoreEnd
    }
}
