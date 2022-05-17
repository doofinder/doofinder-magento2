<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Helper\SearchEngine;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Hash ID validation backend
 */
class HashIdValidation extends \Magento\Framework\App\Config\Value
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * HashIdValidation constructor.
     *
     * @param StoreConfig $storeConfig
     * @param SearchEngine $searchEngine
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        StoreConfig $storeConfig,
        SearchEngine $searchEngine,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchEngine = $searchEngine;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save configuration.
     *
     * @return HashIdValidation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function beforeSave(): HashIdValidation
    {
        if ($hashId = $this->getValue()) {
            $this->validateUnique($hashId);
            $this->validateSearchEngine($hashId);
        }
        if ($this->storeConfig->isSingleStoreMode()) {
            $this->setScope(\Magento\Store\Model\ScopeInterface::SCOPE_STORES);
            $this->setScopeId($this->storeConfig->getCurrentStore()->getId());
        }

        return parent::beforeSave();
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getValue(): ?string
    {
        if ($this->storeConfig->isSingleStoreMode()) {
            if ($this->storeConfig->isSaveAction()) {
                return parent::getValue();
            }
            return $this->storeConfig->getHashId(
                $this->storeConfig->getCurrentStoreId()
            );
        }

        return parent::getValue();
    }

    /**
     * Check if hash id is unique in store.
     *
     * @param string $hashId
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\ValidatorException Hash ID already used.
     */
    private function validateUnique(string $hashId)
    {
        $currentStoreCode = $this->storeConfig->getCurrentStoreCode();
        foreach ($this->storeConfig->getAllStores(false) as $store) {
            // Do not check current store
            if ($currentStoreCode == $store->getCode()) {
                continue;
            }
            $scopeHashId = $this->storeConfig->getHashId((int)$store->getId());
            if ($hashId == $scopeHashId) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    __('HashID %1 is already used in %2 store. It must be unique.', $hashId, $store->getCode())
                );
            }
        }
    }

    /**
     * Check if hash id is available for current api key.
     *
     * @param string $hashId
     * @return void
     * @throws \Magento\Framework\Exception\ValidatorException Search engine unavailable.
     */
    private function validateSearchEngine(string $hashId)
    {
        if (!$this->storeConfig->getApiKey()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Provide API key in the Default Config store view before setting HashID.')
            );
        }
        $searchEngines = [];
        try {
            $searchEngines = $this->searchEngine->getSearchEngines();
        } catch (\Exception $e) {
            $this->_logger->error('There was an error while getting search engines: ' . $e->getMessage());
        }
        if (!isset($searchEngines[$hashId])) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Search engine with HashID %1 does not exist in your account.', $hashId)
            );
        }
    }
}
