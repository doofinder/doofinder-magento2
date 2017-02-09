<?php

namespace Doofinder\Feed\Search;

class IndexStructure implements \Magento\Framework\Indexer\IndexStructureInterface
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\IndexStructure
     */
    protected $_indexStructure;

    /**
     * @param \Magento\CatalogSearch\Model\Indexer\IndexStructure
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Indexer\IndexStructure $indexStructure
    ) {
        $this->_indexStructure = $indexStructure;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
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
        $this->_indexStructure->create($index, $fields, $dimensions);
    }
}
