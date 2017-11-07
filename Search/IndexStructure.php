<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Indexer\IndexStructureInterface;

class IndexStructure implements IndexStructureInterface
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexStructure
     */
    private $_indexStructure;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_search;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexStructureFactory $indexStructureFactory
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\IndexStructureFactory $indexStructureFactory,
        \Doofinder\Feed\Helper\Search $search,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_indexStructure = $indexStructureFactory->create();
        $this->_search = $search;
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
        $this->action('deleteDoofinderIndex', $dimensions);
        $this->_indexStructure->delete($index, $dimensions);
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $this->action('createDoofinderIndex', $dimensions);
        $this->_indexStructure->create($index, $fields, $dimensions);
    }

    /**
     * Action helper
     *
     * @param string $method
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     */
    private function action($method, array $dimensions)
    {
        $originalStoreCode = $this->_storeConfig->getStoreCode();
        $storeId = $this->_search->getStoreIdFromDimensions($dimensions);
        $this->_storeManager->setCurrentStore($storeId);

        $this->_search->{$method}();

        $this->_storeManager->setCurrentStore($originalStoreCode);
    }
}
