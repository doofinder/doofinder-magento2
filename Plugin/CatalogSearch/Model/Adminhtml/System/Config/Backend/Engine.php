<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend;

/**
 * Engine plugin
 */
class Engine
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Model\Api\SearchEngine $searchEngine
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Model\Api\SearchEngine $searchEngine,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchEngine = $searchEngine;
        $this->messageManager = $messageManager;
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
        $storeConfig = $this->storeConfig;

        // Fixes T_PAAMAYIM_NEKUDOTAYIM
        if ($engine->getValue() != $storeConfig::DOOFINDER_SEARCH_ENGINE_NAME) {
            return $value;
        }

        if (!$storeConfig->getApiKey()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Provide API key before enabling Doofinder search engine.')
            );
        }

        if (!$storeConfig->getManagementServer() || !$storeConfig->getSearchServer()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Please configure Search and/or Management server address before enabling Doofinder search engine.')
            );
        }

        $searchEngines = $this->searchEngine->getSearchEngines();
        foreach ($storeConfig->getStoreCodes(false) as $storeCode) {
            $hashId = $storeConfig->getHashId($storeCode);
            if (!$hashId) {
                throw new \Magento\Framework\Exception\ValidatorException(__(
                    'HashID for store %1 is required. ' .
                    'Please, set it before enabling Doofinder search engine.',
                    $storeCode
                ));
            }

            if (!isset($searchEngines[$hashId])) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    __('Search engine with HashID %1 does not exist in your account.', $hashId)
                );
            }
        }

        return $value;
    }
}
