<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Start time backend
 */
class StartTime extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    private $timezone;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->timezone = $timezone;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Convert timezones
     *
     * @return mixed
     */
    public function beforeSave()
    {
        list($hours, $minutes, $seconds) = $this->getValue();
        $date = $this->timezone->date();
        $date->setTime($hours, $minutes, $seconds);
        $date = $this->timezone->date($date, null, false);

        $this->setValue(explode(',', $date->format('H,i,s')));

        return parent::beforeSave();
    }

    /**
     * Convert timezones
     *
     * @return mixed
     */
    public function afterLoad()
    {
        list($hours, $minutes, $seconds) = explode(',', $this->getValue());
        $date = $this->timezone->date(null, null, false);
        $date->setTime($hours, $minutes, $seconds);
        $date = $this->timezone->date($date);

        $this->setValue($date->format('H,i,s'));

        return parent::afterLoad();
    }
}
