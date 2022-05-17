<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

interface FetcherInterface
{
    /**
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, int $storeId);

    /**
     * @param integer $productId
     * @return array
     */
    public function get(int $productId): array;

    /**
     * @return void
     */
    public function clear();
}
