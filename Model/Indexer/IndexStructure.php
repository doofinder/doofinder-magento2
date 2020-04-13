<?php

namespace Doofinder\Feed\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * Class IndexStructure
 * The class responsible for managing operations on Doofinder index schema
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $indxerHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * IndexStructure constructor.
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Helper\Indexer $indexerHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Doofinder\Feed\Helper\Search $search,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\Indexer $indexerHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->search = $search;
        $this->storeConfig = $storeConfig;
        $this->indxerHelper = $indexerHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed
     */
    public function delete($index, array $dimensions = [])
    {
        // phpcs:enable
        $this->action('deleteDoofinderIndex', $dimensions);
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceBeforeLastUsed
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        // phpcs:enable
        $this->action('createDoofinderIndex', $dimensions);
    }

    /**
     * Action helper
     *
     * @param string $method
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return void
     */
    private function action($method, array $dimensions)
    {
        $originalStoreCode = $this->storeConfig->getStoreCode();
        $storeId = $this->indxerHelper->getStoreIdFromDimensions($dimensions);
        $this->storeManager->setCurrentStore($storeId);

        $this->search->{$method}();

        $this->storeManager->setCurrentStore($originalStoreCode);
    }
}
