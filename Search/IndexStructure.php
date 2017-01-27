<?php

namespace Doofinder\Feed\Search;

class IndexStructure implements \Magento\Framework\Indexer\IndexStructureInterface
{
    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions = [])
    {
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
    }
}
