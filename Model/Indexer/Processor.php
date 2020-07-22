<?php

namespace Doofinder\Feed\Model\Indexer;

use Doofinder\Feed\Model\Api\Indexer;

/**
 * Class Processor
 * The class responsible for managing data on Doofinder Index
 */
class Processor
{
    /**
     * @var Indexer
     */
    private $api;

    /**
     * Processor constructor.
     * @param Indexer $indexer
     */
    public function __construct(
        Indexer $indexer
    ) {
        $this->api = $indexer;
    }

    /**
     * Add products to new Doofinder index
     * @param array $products
     * @param array $dimensions
     * @return void
     */
    public function add(array $products, array $dimensions)
    {
        if (empty($products)) {
            return;
        }
        $products = array_values($products);
        $this->api->addItems($products, $dimensions, IndexStructure::INDEX_NAME);
    }

    /**
     * Update created items
     * @param array $products
     * @param array $dimensions
     * @return void
     */
    public function update(array $products, array $dimensions)
    {
        if (empty($products)) {
            return;
        }
        $products = array_values($products);
        $this->api->updateItems($products, $dimensions, IndexStructure::INDEX_NAME);
    }

    /**
     * @param array $products
     * @param array $dimensions
     * @return void
     */
    public function delete(array $products, array $dimensions)
    {
        if (empty($products)) {
            return;
        }
        $this->api->deleteItems($products, $dimensions, IndexStructure::INDEX_NAME);
    }

    /**
     * Replace temporary index with the main one
     * @param array $dimensions
     * @return void
     */
    public function switchIndex(array $dimensions)
    {
        $this->api->switchIndex($dimensions, IndexStructure::INDEX_NAME);
    }
}
