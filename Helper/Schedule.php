<?php

namespace Doofinder\Feed\Helper;

/**
 * Schedule helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Schedule extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    private $cronFactory;

    /**
     * @var \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory
     */
    private $cronCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Doofinder\Feed\Logger\FeedFactory
     */
    private $feedLoggerFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Model\CronFactory $cronFactory
     * @param \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory $cronColFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Doofinder\Feed\Logger\Feed $logger
     * @param \Doofinder\Feed\Logger\FeedFactory $feedLoggerFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codingStandardsIgnoreStart
     * Ignore MEQP2.Classes.ConstructorOperations.CustomOperationsFound
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Model\CronFactory $cronFactory,
        \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory $cronColFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Doofinder\Feed\Logger\Feed $logger,
        \Doofinder\Feed\Logger\FeedFactory $feedLoggerFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
    // @codingStandardsIgnoreEnd
        $this->messageManager = $messageManager;
        $this->generatorFactory = $generatorFactory;
        $this->cronFactory = $cronFactory;
        $this->cronCollectionFactory = $cronColFactory;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        $this->storeConfig = $storeConfig;
        $this->feedConfig = $feedConfig;
        $this->dateTime = $dateTime;
        $this->feedLoggerFactory = $feedLoggerFactory;
        $this->filesystem = $filesystem;
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
        return $this->storeConfig->getStoreConfig($storeCode);
    }

    /**
     * Convert time array to \DateTime
     *
     * @param  array          $time        In format: [hours, minutes, seconds].
     * @param  boolean        $useTimezone
     * @param  \DateTime|null $base        Base date.
     * @return \DateTime
     */
    public function timeArrayToDate(
        array $time,
        $useTimezone = false,
        \DateTime $base = null
    ) {
        $date = $this->timezone->date($base, null, $useTimezone);
        $date->setTime($time[0], $time[1], $time[2]);

        return $date;
    }

    /**
     * Get time for schedule.
     *
     * @param  \DateTime      $date
     * @param  null|\DateTime $now  Date used for testing purposes.
     * @return \DateTime
     */
    public function getScheduleDate(
        \DateTime $date,
        \DateTime $now = null
    ) {
        $now = $now ? $now : $this->timezone->date(null, null, false);
        $start = clone $date;

        if ($start < $now) {
            $start->modify('+1 day');
        }

        return $start;
    }

    /**
     * Regenerate finished shcedules.
     *
     * @param boolean $reset
     * @param boolean $now
     * @param boolean $force
     * @return void
     */
    public function regenerateSchedule($reset = false, $now = false, $force = false)
    {
        foreach ($this->storeConfig->getStoreCodes() as $storeCode) {
            $store = $this->storeManager->getStore($storeCode);
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
        $process = $this->cronFactory->create()->load($storeCode, 'store_code');
        return $process->getEntityId() ? $process : null;
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
     * @return \Doofinder\Feed\Model\Cron
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateProcess(
        \Magento\Store\Model\Store $store,
        $reset = false,
        $now = false,
        $force = false
    ) {
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
                $this->messageManager->addSuccess(__(
                    'Process for store "%1" has been disabled',
                    $store->getName()
                ));
                $this->removeTmpXml($store->getCode());

                $this->disableProcess($process);
            }

            return $process;
        }

        // Do not process the schedule if it has insufficient file permissions
        if (!$this->checkFeedFilePermission($store->getCode())) {
            $this->messageManager->addError(
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
            $this->messageManager->addSuccess(__(
                'Process for store "%1" has been rescheduled',
                $store->getName()
            ));
            $this->removeTmpXml($store->getCode());

            // Override time if $now is enabled
            if ($now) {
                $date = $this->timezone->date(null, null, false);
            } else {
                $date = $this->timeArrayToDate($config['start_time']);
            }

            $this->rescheduleProcess($process, $this->getScheduleDate($date));
        }

        return $process;
    }

    /**
     * Register a new process
     *
     * @param  string $storeCode
     * @return \Doofinder\Feed\Model\Cron
     */
    private function registerProcess($storeCode = 'default')
    {
        $config = $this->getStoreConfig($storeCode);

        $process = $this->cronFactory->create();

        if (empty($status)) {
            $status = $config['enabled'] ? $process::STATUS_WAITING : $process::STATUS_DISABLED;
        }

        $data = [
            'store_code'    =>  $storeCode,
            'status'        =>  $status,
            'message'       =>  $process::MSG_EMPTY,
            'complete'      =>  '-',
            'next_run'      =>  '-',
            'next_iteration'=>  '-',
            'last_feed_name'=>  'None',
        ];

        $process
            ->setData($data)
            ->save();

        $this->_logger->info('Process has been registered', ['process' => $process]);

        return $process;
    }

    /**
     * Enable the process
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @return void
     */
    private function enableProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $process->enable();
        $this->_logger->info('Process has been enabled', ['process' => $process]);
    }

    /**
     * Disable the process
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @return void
     */
    private function disableProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $process->disable();
        $this->_logger->info('Process has been disabled', ['process' => $process]);
    }

    /**
     * Get feed file name
     *
     * @param  string $storeCode
     * @param  string|boolean $password
     * @return string
     */
    public function getFeedFilename($storeCode, $password = null)
    {
        $filename = 'doofinder-' . $storeCode;

        if ($password === true) {
            $config = $this->getStoreConfig($storeCode);
            $password = $config['password'];
        }

        if ($password) {
            $filename .= '-' . $password;
        }

        $filename .= '.xml';

        return  $filename;
    }

    /**
     * Get feed temporary file name
     *
     * @param  string $storeCode
     * @return string
     */
    private function getFeedTmpFilename($storeCode)
    {
        return $this->getFeedFilename($storeCode) . '.tmp';
    }

    /**
     * Get feed lock file name
     *
     * @param  string $storeCode
     * @return string
     */
    private function getFeedLockFilename($storeCode)
    {
        return $this->getFeedFilename($storeCode) . '.lock';
    }

    /**
     * Remove tmp xml file.
     *
     * @param  string $storeCode
     * @return boolean
     */
    private function removeTmpXml($storeCode)
    {
        $tmpFilename = $this->getFeedTmpFilename($storeCode);

        $tmpDir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        if ($tmpDir->isExist($tmpFilename)) {
            if ($tmpDir->delete($tmpFilename)) {
                $this->messageManager->addSuccess(__('Temporary xml file: %1 has beed removed.', $tmpFilename));
                return true;
            } else {
                $this->messageManager->addError(
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
     * @param  string $storeCode
     * @return boolean
     */
    public function isFeedFileExist($storeCode)
    {
        $filename = $this->getFeedFilename($storeCode);
        $directory = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );

        return $directory->isExist($filename);
    }

    /**
     * Get feed file url.
     *
     * @param  string $storeCode
     * @param  string $withPassword
     * @return string
     */
    public function getFeedFileUrl($storeCode, $withPassword = true)
    {
        $filename = $this->getFeedFilename($storeCode, $withPassword);
        $baseUrl = $this->storeManager->getStore($storeCode)->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        return $baseUrl . $filename;
    }

    /**
     * Validate file permissions for feed generation.
     *
     * @param  string $storeCode
     * @return boolean
     */
    private function checkFeedFilePermission($storeCode)
    {
        $filename = $this->getFeedFilename($storeCode);
        $tmpFilename = $this->getFeedTmpFilename($storeCode);

        $dir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $tmpDir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

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
     * @param  \Doofinder\Feed\Model\Cron $process
     * @param  \DateTime $date
     * @return void
     */
    private function rescheduleProcess(\Doofinder\Feed\Model\Cron $process, \DateTime $date)
    {
        $process->setStatus($process::STATUS_PENDING)
            ->setComplete('0%')
            ->setNextRun($this->dateTime->formatDate($date->getTimestamp()))
            ->setNextIteration($this->dateTime->formatDate($date->getTimestamp()))
            ->setOffset(0)
            ->setMessage($process::MSG_PENDING)
            ->setErrorStack(0)
            ->setCreatedAt($this->dateTime->formatDate(time()))
            ->save();

        $this->_logger->info('Process has been scheduled', ['process' => $process]);
    }

    /**
     * Schedule the running process.
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @return void
     */
    private function scheduleProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $config = $this->storeConfig->getStoreConfig($process->getStoreCode());

        // Set new schedule time
        $delayInMin = (int) $config['step_delay'];
        $timeScheduled = $this->timeArrayToDate([date('H'), date('i') + $delayInMin, date('s')]);

        // Set process data and save
        $process
            ->setStatus($process::STATUS_RUNNING)
            ->setNextRun('-')
            ->setNextIteration($this->dateTime->formatDate($timeScheduled))
            ->save();

        $this->_logger->info(
            __('Scheduling the next step for %1', $this->dateTime->formatDate($timeScheduled)),
            ['process' => $process]
        );
    }

    /**
     * Concludes process.
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @return void
     */
    private function endProcess(\Doofinder\Feed\Model\Cron $process)
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
        $collection = $this->cronCollectionFactory->create();

        $collection
            ->addFieldToFilter('status', [
                'in' => [
                    \Doofinder\Feed\Model\Cron::STATUS_PENDING,
                    \Doofinder\Feed\Model\Cron::STATUS_RUNNING,
                ]
            ])
            ->addFieldToFilter('next_iteration', [
                'lteq' => $this->dateTime->formatDate($this->getNowDate())
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
    private function getNowDate()
    {
        return $this->timezone->date(null, null, false);
    }

    /**
     * Lock process
     *
     * Locking process ensures that no other
     * cron job runs it at the same time
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @param  boolean                    $remove  Should the lock be removed instead of created.
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException Process is locked.
     */
    private function lockProcess(\Doofinder\Feed\Model\Cron $process, $remove = false)
    {
        $tmpDir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $lockFilename = $this->getFeedLockFilename($process->getStoreCode());

        // Create lock file
        if (!$remove) {
            if ($tmpDir->isExist($lockFilename)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Process for store %1 is already locked',
                        $process->getStoreCode()
                    )
                );
            }

            $tmpDir->touch($lockFilename);
        } else {
            $tmpDir->delete($lockFilename);
        }
    }

    /**
     * Unlock process
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @return void
     */
    private function unlockProcess(\Doofinder\Feed\Model\Cron $process)
    {
        $this->lockProcess($process, true);
    }

    /**
     * Run process
     *
     * @param  \Doofinder\Feed\Model\Cron $process
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException Feed file could not be renamed.
     */
    public function runProcess(\Doofinder\Feed\Model\Cron $process)
    {
        // Lock process
        $this->lockProcess($process);

        $storeCode = $process->getStoreCode();

        // Set current store for generator
        $this->storeManager->setCurrentStore($storeCode);

        $config = $this->storeConfig->getStoreConfig($storeCode);

        $feedConfig = $this->feedConfig->getFeedConfig($storeCode, [
            'offset' => $process->getOffset(),
            'limit' => $config['step_size'],
        ]);

        // Set destination file
        $tmpDir = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $tmpFilename = $this->getFeedTmpFilename($storeCode);
        $feedConfig['data']['config']['processors']['Xml']['destination_file'] =
            $tmpDir->getAbsolutePath($tmpFilename);

        // Prepare logger with process context
        $logger = $this->feedLoggerFactory->create(['process' => $process]);

        // Create generator
        $generator = $this->generatorFactory->create($feedConfig, [
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
                $dir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                $tmpDir = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

                if (!$tmpDir->getDriver()->rename(
                    $tmpDir->getAbsolutePath($tmpFilename),
                    $dir->getAbsolutePath($filename),
                    $dir->getDriver()
                )) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            'Cannot rename %1 to %2',
                            $tmpFilename,
                            $filename
                        )
                    );
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

        // Unlock process
        $this->unlockProcess($process);
    }
}
