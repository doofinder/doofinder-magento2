<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Class HashIdValidation
 * @package Doofinder\Feed\Model\Config\Backend
 */
class HashIdValidation extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * HashIdValidation constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeConfig = $storeConfig;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save configuration.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function save()
    {
        $hashId = $this->getValue();

        if ($this->isUnique($hashId)) {
            return parent::save();
        }
    }

    /**
     * Check if hash id is unique in store.
     *
     * @param string $hashId
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    private function isUnique($hashId)
    {
        $currentStoreCode = $this->_storeConfig->getStoreCode();

        foreach ($this->_storeConfig->getStoreCodes(false) as $storeCode) {
            // Do not check current store
            if ($currentStoreCode == $storeCode) {
                continue;
            }

            $scopeHashId = $this->_storeConfig->getHashId($storeCode);

            if ($hashId && $hashId == $scopeHashId) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    __('HashID %1 is already used in %2 store. It must have a unique value.', $hashId, $storeCode)
                );
            }
        }

        return true;
    }
}
