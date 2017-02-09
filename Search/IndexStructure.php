<?php

namespace Doofinder\Feed\Search;

class IndexStructure implements \Magento\Framework\Indexer\IndexStructureInterface
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexStructure
     */
    protected $_indexStructure;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    protected $_search;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexStructure
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\IndexStructure $indexStructure,
        \Doofinder\Feed\Helper\Search $search,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_indexStructure = $indexStructure;
        $this->_search = $search;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
        $this->action('deleteIndex');

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
        $this->action('createIndex');

        $this->_indexStructure->create($index, $fields, $dimensions);
    }

    /**
     * Action helper
     *
     * @param string $method
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     */
    protected function action($method, array $dimensions)
    {
        $originalStoreCode = $this->_storeConfig->getStoreCode();
        $storeId = $this->getStoreId($dimensions);
        $this->_storeManager->setCurrentStore($storeId);

        $this->_search->{$method}();

        $this->_storeManager->setCurrentStore($originalStoreId);
    }

    /**
     * Get store id from dimensions
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return int
     */
    public function getStoreId(array $dimensions)
    {
        foreach ($dimensions as $dimension) {
            if ($dimension->getName() == 'scope') {
                return $dimension->getValue();
            }
        }

        return null;
    }
}
