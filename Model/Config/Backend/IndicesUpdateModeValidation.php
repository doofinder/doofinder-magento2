<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Hash ID validation backend
 */
class IndicesUpdateModeValidation extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Model\Api\SearchEngine
     */
    private $searchEngine;

    /**
     * HashIdValidation constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Model\Api\SearchEngine $searchEngine
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig                      $storeConfig,
        \Doofinder\Feed\Model\Api\SearchEngine                  $searchEngine,
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface      $config,
        \Magento\Framework\App\Cache\TypeListInterface          $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        array                                                   $data = []
    )
    {
        $this->storeConfig = $storeConfig;
        $this->searchEngine = $searchEngine;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save configuration.
     *
     * @return mixed
     */
    public function save()
    {
        $updateMode = $this->getValue();
        if ($updateMode == \Doofinder\Feed\Helper\StoreConfig::DOOFINDER_INDICES_UPDATE_API) {
            $this->validateHashIdAndApiKey();
        }

        if ($this->storeConfig->isSingleStoreMode()) {
            $this->setScope(\Magento\Store\Model\ScopeInterface::SCOPE_STORES);
            $this->setScopeId($this->storeConfig->getCurrentStore()->getId());
        }

        return parent::save();
    }

    /**
     * Check if hash id and api key are set.
     *
     * @return void
     * @throws \Magento\Framework\Exception\ValidatorException Hash id or API Key empty.
     */
    private function validateHashIdAndApiKey()
    {
        if (!$this->storeConfig->getApiKey()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Provide API key in the Default Config store view before setting HashID.')
            );
        }

        $hashId = $this->storeConfig->getHashId($this->storeConfig->getCurrentStoreCode());
        if (!$hashId && array_key_exists('hash_id', $this->getFieldsetData())) {
            // assess if hash_id is being updated at the same time
            $hashId = $this->getFieldsetData()['hash_id'];
        }
        if (!$hashId) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('HashID cannot be empty when Doofinder update mode is "Doofinder Api".')
            );
        }
    }
}
