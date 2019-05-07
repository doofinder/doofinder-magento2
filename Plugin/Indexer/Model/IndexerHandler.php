<?php

namespace Doofinder\Feed\Plugin\Indexer\Model;

/**
 * Plugin IndexerHandler
 */
class IndexerHandler
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
    }

    /**
     * Guards against running when search engine is disabled for store
     *
     * @param \Doofinder\Feed\Search\IndexerHandler $subject
     * @param callable $proceed
     * @param array $dimensions
     * @param \Traversable $documents
     * @return \Magento\Framework\Indexer\SaveHandler\IndexerInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSaveIndex(
        \Doofinder\Feed\Search\IndexerHandler $subject,
        callable $proceed,
        array $dimensions,
        \Traversable $documents
    ) {
        if ($this->canRunIndexer($dimensions)) {
            return $proceed($dimensions, $documents);
        }
    }

    /**
     * Guards against running when search engine is disabled for store
     *
     * @param \Doofinder\Feed\Search\IndexerHandler $subject
     * @param callable $proceed
     * @param array $dimensions
     * @param \Traversable $documents
     * @return \Magento\Framework\Indexer\SaveHandler\IndexerInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDeleteIndex(
        \Doofinder\Feed\Search\IndexerHandler $subject,
        callable $proceed,
        array $dimensions,
        \Traversable $documents
    ) {
        if ($this->canRunIndexer($dimensions)) {
            return $proceed($dimensions, $documents);
        }
    }

    /**
     * Guards against running when search engine is disabled for store
     *
     * @param \Doofinder\Feed\Search\IndexerHandler $subject
     * @param callable $proceed
     * @param array $dimensions
     * @return \Magento\Framework\Indexer\SaveHandler\IndexerInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCleanIndex(
        \Doofinder\Feed\Search\IndexerHandler $subject,
        callable $proceed,
        array $dimensions
    ) {
        if ($this->canRunIndexer($dimensions)) {
            return $proceed($dimensions);
        }
    }

    /**
     * Checks if indexer can be called
     *
     * @param array $dimensions
     * @return boolean
     */
    private function canRunIndexer(array $dimensions)
    {
        // Fixes different key issue across multiple framework versions
        $dimension = reset($dimensions);
        $store = $this->storeManager->getStore($dimension->getValue());

        $isHashIdEnable = $this->storeConfig->isStoreSearchEngineEnabled($store->getCode());
        $isDoofinderSearch = $this->storeConfig->isInternalSearchEnabled($store->getCode());

        return $isHashIdEnable || !$isDoofinderSearch;
    }
}
