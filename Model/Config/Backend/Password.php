<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Password backend
 */
class Password extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        $this->schedule = $schedule;
        $this->storeConfig = $storeConfig;
        $this->filesystem = $filesystem;

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
     * Validate password
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException Password invalid.
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
     * Rename feed
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException Feed file could not be renamed.
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            foreach ($this->storeConfig->getStoreCodes(false) as $storeCode) {
                $oldFilename = $this->schedule->getFeedFilename($storeCode, $this->getOldValue());
                $newFilename = $this->schedule->getFeedFilename($storeCode, $this->getValue());

                $directory = $this->filesystem->getDirectoryWrite(
                    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
                );

                if ($directory->isExist($oldFilename)) {
                    if (!$directory->isExist($newFilename)) {
                        $directory->renameFile($oldFilename, $newFilename);
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(__(
                            'Feed file could not be renamed accordingly to new %1 ' .
                            'value because file with name %2 already exists.',
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
