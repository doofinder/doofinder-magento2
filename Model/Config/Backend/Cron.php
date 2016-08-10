<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Class Cron
 * @package Doofinder\Feed\Model\Config\Backend
 */
class Cron extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/feed_generate/schedule/cron_expr';
    const XML_PATH_FEED_GENERATE_ENABLED = 'groups/feed_cron/fields/enabled/value';
    const XML_PATH_FEED_GENERATE_BACKUP_TIME = 'groups/feed_cron/fields/start_time/value';
    const XML_PATH_FEED_GENERATE_FREQUENCY = 'groups/feed_cron/fields/frequency/value';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * Cron constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save cron expression.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $enabled = $this->getData(self::XML_PATH_FEED_GENERATE_ENABLED);
        $time = $this->getData(self::XML_PATH_FEED_GENERATE_BACKUP_TIME);
        $frequency = $this->getData(self::XML_PATH_FEED_GENERATE_FREQUENCY);

        $frequencyWeekly = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequencyMonthly = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronExprArray = [
                intval($time[1]),                                 # Minute
                intval($time[0]),                                 # Hour
                $frequency == $frequencyMonthly ? '1' : '*',      # Day of the Month
                '*',                                              # Month of the Year
                $frequency == $frequencyWeekly ? '1' : '*',       # Day of the Week
            ];
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cron job could not be saved'));
        }

        return parent::afterSave();
    }
}