<?php

namespace Doofinder\Feed\Search;

class IndexStructure extends \Magento\CatalogSearch\Model\Indexer\IndexStructure
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    protected $_search;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Doofinder\Feed\Helper\Search $search
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Doofinder\Feed\Helper\Search $search,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
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

        parent::delete($index, $dimensions);
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

        parent::create($index, $fields, $dimensions);
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
        $storeId = $this->_search->getStoreIdFromDimensions($dimensions);
        $this->_storeManager->setCurrentStore($storeId);

        $this->_search->{$method}();

        $this->_storeManager->setCurrentStore($originalStoreId);
    }
}
