<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Hash ID validation backend
 */
class HashIdValidation extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * HashIdValidation constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Search $search
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
        \Doofinder\Feed\Helper\Search $search,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->search = $search;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save configuration.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException Hash id cannot be empty.
     */
    public function save()
    {
        if ($hashId = $this->getValue()) {
            $this->validateUnique($hashId);
            $this->validateSearchEngine($hashId);
        } elseif ($this->storeConfig->isInternalSearchEnabled()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('HashID cannot be empty when Doofinder engine is enabled.')
            );
        }

        if ($this->storeConfig->isSingleStoreMode()) {
            $this->setScope(\Magento\Store\Model\ScopeInterface::SCOPE_STORES);
            $this->setScopeId($this->storeConfig->getCurrentStore()->getId());
        }

        return parent::save();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        if ($this->storeConfig->isSingleStoreMode()) {
            if ($this->storeConfig->isSaveAction()) {
                return parent::getValue();
            }
            return $this->storeConfig->getHashId(
                $this->storeConfig->getCurrentStoreCode()
            );
        }
        return parent::getValue();
    }

    /**
     * Check if hash id is unique in store.
     *
     * @param  string $hashId
     * @return void
     * @throws \Magento\Framework\Exception\ValidatorException Hash ID already used.
     */
    private function validateUnique($hashId)
    {
        $currentStoreCode = $this->storeConfig->getCurrentStoreCode();
        $storeCodes = $this->storeConfig->getStoreCodes(false, true);
        foreach ($storeCodes as $storeCode) {
            // Do not check current store
            if ($currentStoreCode == $storeCode) {
                continue;
            }

            $scopeHashId = $this->storeConfig->getHashId($storeCode);

            if ($hashId == $scopeHashId) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    __('HashID %1 is already used in %2 store. It must be unique.', $hashId, $storeCode)
                );
            }
        }
    }

    /**
     * Check if hash id is available for current api key.
     *
     * @param  string $hashId
     * @return void
     * @throws \Magento\Framework\Exception\ValidatorException Search engine unavailable.
     */
    private function validateSearchEngine($hashId)
    {
        if (!$apiKey = $this->storeConfig->getApiKey()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Provide API key in the Default Config store view before setting HashID.')
            );
        }

        $searchEngines = $this->search->getDoofinderSearchEngines($apiKey);

        if (!isset($searchEngines[$hashId])) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Search engine with HashID %1 does not exist in your account.', $hashId)
            );
        }
    }
}
