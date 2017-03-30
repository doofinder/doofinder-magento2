<?php

namespace Doofinder\Feed\Helper;

/**
 * Class Schedule
 *
 * @package Doofinder\Feed\Helper
 */
class Schedule extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    protected $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    protected $_cronFactory;

    /**
     * @var \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory
     */
    protected $_cronCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_feedConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    protected $_logger;

    /**
     * @var \Doofinder\Feed\Logger\FeedFactory
     */
    protected $_feedLoggerFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Model\CronFactory $cronFactory
     * @param \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory $cronCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Doofinder\Feed\Logger\Feed $logger
     * @param \Doofinder\Feed\Logger\FeedFactory $feedLoggerFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Model\CronFactory $cronFactory,
        \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory $cronCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Doofinder\Feed\Logger\Feed $logger,
        \Doofinder\Feed\Logger\FeedFactory $feedLoggerFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_messageManager = $messageManager;
        $this->_generatorFactory = $generatorFactory;
        $this->_cronFactory = $cronFactory;
        $this->_cronCollectionFactory = $cronCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_timezone = $timezone;
        $this->_storeConfig = $storeConfig;
        $this->_feedConfig = $feedConfig;
        $this->_dateTime = $dateTime;
        $this->_feedLoggerFactory = $feedLoggerFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context);

        // Override AbstractHelper's logger
        $this->_logger = $logger;
    }

    /**
     * Get store config.
     *
     * @param null|string $storeCode
     * @return array
     */
    public function getStoreConfig($storeCode = null)
    {
        return $this->_storeConfig->getStoreConfig($storeCode);
    }

    /**
     * Convert time array to \DateTime
     *
     * @param array $time - [hours, minutes, seconds]
     * @param string|null $timezone
     * @param \DateTime|null $base - Base \DateTime
     * @return \DateTime
     */
    public function timeArrayToDate(array $time, $timezone = null, $base = null)
    {
        if (!$timezone) {
            $timezone = date_default_timezone_get();
        }

        $date = new \DateTime(
            $base ? $base->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT) : null,
            new \DateTimeZone($timezone)
        );
        $date->setTime($time[0], $time[1], $time[2]);

        return $date;
    }

    /**
     * Get time for schedule.
     *
     * @param \DateTime $date
     * @param string $frequency
     * @param null|\DateTime $now - Date used for testing purposes
     * @return \DateTime
     */
    public function getScheduleDate($date, $frequency, $now = null)
    {
        $now = $now ? $now : new \DateTime();
        $start = clone $date;

        if ($start < $now) {
            switch ($frequency) {
                case \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY:
                    $start->modify('+1 month');
                    break;

                case \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY:
                    $start->modify('+7 days');
                    break;

                case \Magento\Cron\Model\Config\Source\Frequency::CRON_DAILY:
                    $start->modify('+1 day');
                    break;
            }
        }

        return $start;
    }

    /**
     * Regenerate finished shcedules.
     *
     * @param boolean $reset = false
     * @param boolean $now = false
     * @param boolean $force = false
     */
    public function regenerateSchedule($reset = false, $now = false, $force = false)
    {
        foreach ($this->_storeConfig->getStoreCodes() as $storeCode) {
            $store = $this->_storeManager->getStore($storeCode);
            $this->updateProcess($store, $reset, $now, $force);
        }
    }

    /**
     * Gets process for given store code
     *
     * @param string $storeCode
     * @return \Doofinder\Feed\Model\Cron
     */
    public function getProcessByStoreCode($storeCode = 'default')
    {
        $process = $this->_cronFactory->create()->load($storeCode, 'store_code');
        return $process->getId() ? $process : null;
    }

    /**
     * Checks if process is registered in doofinder cron table
     *
     * @param string $storeCode
     * @return bool
     */
    protected function isProcessRegistered($storeCode = 'default')
    {
        $process = $this->getProcessByStoreCode($storeCode);
        return $process ? true : false;
    }

    /**
     * Update process for given store code.
     * If process does not exits - create it.
     * Reschedule the process if it needs it.
     *
     * @param \Magento\Store\Model\Store $store
     * @param boolean $reset
     * @param boolean $now
     * @param boolean $force
     *
     * @return \Doofinder\Feed\Model\Cron
     */
    public function updateProcess(\Magento\Store\Model\Store $store, $reset = false, $now = false, $force = false)
    {
        // Get store config
        $config = $this->getStoreConfig($store->getCode());

        // Try loading store process
        $process = $this->getProcessByStoreCode($store->getCode());

        // Create new process if it not exists
        if (!$process) {
            $process = $this->registerProcess($store->getCode());
        }

        // Enable/disable process if it needs to
        if ($config['enabled'] || $force) {
            if ($process->isEnabled()) {
                $this->enableProcess($process);
            }
        } else {
            if (!$process->isEnabled()) {
                $this->_messageManager->addSuccess(__('Process for store "%1" has been disabled', $store->getName()));
                $this->removeTmpXml($store->getCode());

                $this->disableProcess($process);
            }

            return $process;
        }

        // Do not process the schedule if it has insufficient file permissions
        if (!$this->checkFeedFilePermission($store->getCode())) {
            $this->_messageManager->addError(
                __(
                    'Insufficient file permissions for store: %1. ' .
                    'Check if the feed file is writeable',
                    $store->getName()
                )
            );
            return $process;
        }

        // Reschedule the process if it needs to
        if ($reset || $process->isWaiting()) {
            $this->_messageManager->addSuccess(__('Process for store "%1" has been rescheduled', $store->getName()));
            $this->removeTmpXml($store->getCode());

            // Override time if $now is enabled
            if ($now) {
                $date = new \DateTime(null, new \DateTimeZone($this->_timezone->getDefaultTimezone()));
            } else {
                $date = $this->timeArrayToDate($config['start_time'], $this->_timezone->getDefaultTimezone());
            }

            $this->rescheduleProcess($process, $this->getScheduleDate($date, $config['frequency']));
        }

        return $process;
    }

    /**
     * Register a new process
     *
     * @param string $storeCode = 'default'
     * @return \Doofinder\Feed\Model\Cron
     */
    protected function registerProcess($storeCode = 'default')
    {
        $config = $this->getStoreConfig($storeCode);

        $process = $this->_cronFactory->create();

        if (empty($status)) {
            $status = $config['enabled'] ? $process::STATUS_WAITING : $process::STATUS_DISABLED;
        }

        $data = array(
            'store_code'    =>  $storeCode,
            'status'        =>  $status,
            'message'       =>  $process::MSG_EMPTY,
            'complete'      =>  '-',
            'next_run'      =>  '-',
            'next_iteration'=>  '-',
            'last_feed_name'=>  'None',
        );

        $process
            ->setData($data)
            ->save();

        $this->_logger->info('Process has been registered', ['process' => $process]);

        return $process;
    }

    /**
     * Enable the process
     *
     * @param \Doofinder\Feed\Model\Cron $process
     */
    protected function enableProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $process->enable();
        $this->_logger->info('Process has been enabled', ['process' => $process]);
    }

    /**
     * Disable the process
     *
     * @param \Doofinder\Feed\Model\Cron
     */
    protected function disableProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $process->disable();
        $this->_logger->info('Process has been disabled', ['process' => $process]);
    }

    /**
     * Get feed file name
     *
     * @param string $storeCode
     * @param string|boolean $password = true
     * @return string
     */
    public function getFeedFilename($storeCode, $password = null)
    {
        $filename = 'doofinder-' . $storeCode;

        if ($password === true) {
            $config = $this->getStoreConfig($storeCode);
            $password = $config['password'];
        }

        if ($password && strlen($password)) {
            $filename .= '-' . $password;
        }

        $filename .= '.xml';

        return  $filename;
    }

    /**
     * Get feed temporary file name
     *
     * @param string $storeCode
     * @return string
     */
    protected function getFeedTmpFilename($storeCode)
    {
        return $this->getFeedFilename($storeCode) . '.tmp';
    }

    /**
     * Remove tmp xml file.
     *
     * @param string $storeCode
     * @return bool
     */
    protected function removeTmpXml($storeCode)
    {
        $tmpFilename = $this->getFeedTmpFilename($storeCode);

        $tmpDir = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        if ($tmpDir->isExist($tmpFilename)) {
            if ($tmpDir->delete($tmpFilename)) {
                $this->_messageManager->addSuccess(__('Temporary xml file: %1 has beed removed.', $tmpFilename));
                return true;
            } else {
                $this->_messageManager->addError(
                    __('Could not remove %1 This can lead to some errors. Remove this file manually.', $tmpFilename)
                );
                return false;
            }
        }

        return false;
    }

    /**
     * Check if feed file exists.
     *
     * @param string $storeCode
     * @return boolean
     */
    public function isFeedFileExist($storeCode)
    {
        $filename = $this->getFeedFilename($storeCode);
        $directory = $this->_filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );

        return $directory->isExist($filename);
    }

    /**
     * Get feed file url.
     *
     * @param string $storeCode
     * @param string $withPassword = true
     * @return string
     */
    public function getFeedFileUrl($storeCode, $withPassword = true)
    {
        $filename = $this->getFeedFilename($storeCode, $withPassword);
        $baseUrl = $this->_storeManager->getStore($storeCode)->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        return $baseUrl . $filename;
    }

    /**
     * Validate file permissions for feed generation.
     *
     * @param string $storeCode
     * @return boolean
     */
    protected function checkFeedFilePermission($storeCode)
    {
        $filename = $this->getFeedFilename($storeCode);
        $tmpFilename = $this->getFeedTmpFilename($storeCode);

        $dir = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $tmpDir = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        if (!$dir->isExist()) {
            $dir->create();
        }
        if (!$tmpDir->isExist()) {
            $tmpDir->create();
        }

        return ($dir->isWritable($filename) || ($dir->isWritable() && !$dir->isExist($filename))) &&
                ($tmpDir->isWritable($tmpFilename) || ($tmpDir->isWritable() && !$tmpDir->isExist($tmpFilename)));
    }

    /**
     * Reschedule the process accordingly to process configuration.
     *
     * @param \Doofinder\Feed\Model\Cron $process
     * @param \DateTime $date
     */
    protected function rescheduleProcess(\Doofinder\Feed\Model\Cron $process, $date)
    {
        $process->setStatus($process::STATUS_PENDING)
            ->setComplete('0%')
            ->setNextRun($this->_dateTime->formatDate($date->getTimestamp()))
            ->setNextIteration($this->_dateTime->formatDate($date->getTimestamp()))
            ->setOffset(0)
            ->setMessage($process::MSG_PENDING)
            ->setErrorStack(0)
            ->setCreatedAt($this->_dateTime->formatDate(time()))
            ->save();

        $this->_logger->info('Process has been scheduled', ['process' => $process]);
    }

    /**
     * Schedule the running process.
     *
     * @param \Doofinder\Feed\Model\Cron $process
     */
    protected function scheduleProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $config = $this->_storeConfig->getStoreConfig($process->getStoreCode());

        // Set new schedule time
        $delayInMin = intval($config['step_delay']);
        $timeScheduled = $this->timeArrayToDate([date('H'), date('i') + $delayInMin, date('s')]);

        // Set process data and save
        $process
            ->setStatus($process::STATUS_RUNNING)
            ->setNextRun('-')
            ->setNextIteration($this->_dateTime->formatDate($timeScheduled))
            ->save();

        $this->_logger->info(
            __('Scheduling the next step for %1', $this->_dateTime->formatDate($timeScheduled)),
            ['process' => $process]
        );
    }

    /**
     * Concludes process.
     *
     * @param \Doofinder\Feed\Model\Cron $process
     */
    protected function endProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $process
            ->setStatus($process::STATUS_WAITING)
            ->setNextRun('-')
            ->setNextIteration('-')
            ->save();
    }

    /**
     * Get active process
     *
     * @return \Doofinder\Feed\Model\Cron
     */
    public function getActiveProcess()
    {
        $collection = $this->_cronCollectionFactory->create();

        $collection
            ->addFieldToFilter('status', [
                'in' => [
                    \Doofinder\Feed\Model\Cron::STATUS_PENDING,
                    \Doofinder\Feed\Model\Cron::STATUS_RUNNING,
                ]
            ])
            ->addFieldToFilter('next_iteration', [
                'lteq' => $this->_dateTime->formatDate($this->getNowDate())
            ])
            ->setOrder('next_iteration', 'asc')
            ->setPageSize(1);

        return $collection->fetchItem();
    }

    /**
     * Get current date in default timezone
     *
     * @return \DateTime
     */
    protected function getNowDate()
    {
        $date = new \DateTime();
        $date->setTimezone(
            new \DateTimeZone($this->_timezone->getDefaultTimezone())
        );

        return $date;
    }

    /**
     * Run process
     *
     * @param \Doofinder\Feed\Model\Cron
     */
    public function runProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $storeCode = $process->getStoreCode();

        // Set current store for generator
        $this->_storeManager->setCurrentStore($storeCode);

        $config = $this->_storeConfig->getStoreConfig($storeCode);

        $feedConfig = $this->_feedConfig->getFeedConfig($storeCode, [
            'offset' => $process->getOffset(),
            'limit' => $config['step_size'],
        ]);

        // Set destination file
        $tmpDir = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $tmpFilename = $this->getFeedTmpFilename($storeCode);
        $feedConfig['data']['config']['processors']['Xml']['destination_file'] =
            $tmpDir->getAbsolutePath($tmpFilename);

        // Prepare logger with process context
        $logger = $this->_feedLoggerFactory->create(['process' => $process]);

        // Create generator
        $generator = $this->_generatorFactory->create($feedConfig, [
            'logger' => $logger,
        ]);

        try {
            // Run generator
            $generator->run();

            $fetcher = $generator->getFetcher('Product');

            // Set process offset and progress
            $process->setOffset($fetcher->getLastProcessedEntityId());
            $process->setComplete(sprintf('%0.1f%%', $fetcher->getProgress() * 100));

            if (!$fetcher->isDone()) {
                $this->scheduleProcess($process);
            } else {
                $this->_logger->info(__('Feed generation completed'), ['process' => $process]);

                $filename = $this->getFeedFilename($storeCode);
                $dir = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                $tmpDir = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

                if (!$tmpDir->getDriver()->rename(
                    $tmpDir->getAbsolutePath($tmpFilename),
                    $dir->getAbsolutePath($filename),
                    $dir->getDriver()
                )) {
                    throw new \Exception(__('Cannot rename %1 to %2', $tmpFilename, $filename));
                }
                $process->setLastFeedName($filename);

                $process->setMessage(__('Last process successfully completed. Now waiting for new schedule.'));
                $this->endProcess($process);
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['process' => $process]);
            $process->setErrorStack($process->getErrorStack() + 1);
            $process->setMessage('#error#' . $e->getMessage());
            $this->scheduleProcess($process);
        }
    }
}
