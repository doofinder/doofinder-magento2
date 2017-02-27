<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Class Password
 * @package Doofinder\Feed\Model\Config\Backend
 */
class Password extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_schedule = $schedule;
        $this->_storeConfig = $storeConfig;
        $this->_filesystem = $filesystem;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if (!preg_match('/^[a-zA-Z0-9_-]*$/', $this->getValue())) {
            $config = $this->getFieldConfig();

            throw new \Magento\Framework\Exception\LocalizedException(__(
                '%1 value is invalid. Only alphanumeric characters with underscores (_) and hyphens (-) are allowed.',
                $config['label']
            ));
        }

        return parent::beforeSave();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            foreach ($this->_storeConfig->getStoreCodes(false) as $storeCode) {
                $oldFilename = $this->_schedule->getFeedFilename($storeCode, $this->getOldValue());
                $newFilename = $this->_schedule->getFeedFilename($storeCode, $this->getValue());

                $directory = $this->_filesystem->getDirectoryWrite(
                    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
                );

                if ($directory->isExist($oldFilename)) {
                    if (!$directory->isExist($newFilename)) {
                        $directory->renameFile($oldFilename, $newFilename);
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(__(
                            'Feed file could not be renamed accordingly to new %1 value because file with name %2 already exists.',
                            $this->getData('field_config/label'),
                            $newFilename
                        ));
                    }
                }
            }
        }

        return parent::afterSave();
    }
}
