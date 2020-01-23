<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update;

/**
 * Interface FetcherInterface
 * The interface for Doofinder index data fetchers
 */
interface FetcherInterface
{
    /**
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, $storeId);

    /**
     * @param integer $productId
     * @return array
     */
    public function get($productId);

    /**
     * @return void
     */
    public function clear();
}
