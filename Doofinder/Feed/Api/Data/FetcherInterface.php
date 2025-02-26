<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

interface FetcherInterface
{
    /**
     * Processes the item's fetched result
     *
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, int $storeId);

    /**
     * Obtains the fetched result
     *
     * @param integer $productId
     * @return array
     */
    public function get(int $productId): array;

    /**
     * Clears the fetched result
     *
     * @return void
     */
    public function clear();
}
