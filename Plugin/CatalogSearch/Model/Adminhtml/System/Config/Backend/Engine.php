<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend;

/**
 * Engine plugin
 */
class Engine
{
    /**
     * Search engine identifier
     */
    const SEARCH_ENGINE = 'doofinder';

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Search $search
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Search $search
    ) {
        $this->storeConfig = $storeConfig;
        $this->search = $search;
    }

    /**
     * Validate if doofinder engine can be enabled
     *
     * @param  \Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine $engine
     * @param  mixed $value
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException Search engine not available.
     */
    public function beforeSave(
        \Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine $engine,
        $value = null
    ) {
        if ($engine->getValue() != $this->storeConfig::DOOFINDER_SEARCH_ENGINE_NAME) {
            return $value;
        }

        if (!$apiKey = $this->storeConfig->getApiKey()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Provide API key before enabling Doofinder search engine.')
            );
        }

        $searchEngines = $this->search->getDoofinderSearchEngines($apiKey);
        foreach ($this->storeConfig->getStoreCodes(false) as $storeCode) {
            if (!$hashId = $this->storeConfig->getHashId($storeCode)) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    __('HashID for store %1 is not provided.', $storeCode)
                );
            }

            if (!isset($searchEngines[$hashId])) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    __('Search engine with HashID %1 is not available.', $hashId)
                );
            }
        }

        return $value;
    }
}
