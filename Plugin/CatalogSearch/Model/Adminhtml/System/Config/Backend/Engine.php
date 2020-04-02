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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Search $search,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->storeConfig = $storeConfig;
        $this->search = $search;
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

        if (!$apiKey = $storeConfig->getApiKey()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Provide API key before enabling Doofinder search engine.')
            );
        }

        $searchEngines = $this->search->getDoofinderSearchEngines($apiKey);
        foreach ($storeConfig->getStoreCodes(false) as $storeCode) {
            if (!$hashId = $storeConfig->getHashId($storeCode)) {
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
