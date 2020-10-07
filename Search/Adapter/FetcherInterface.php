<?php

namespace Doofinder\Feed\Search\Adapter;

use Magento\Framework\Search\RequestInterface;

/**
 * Interface FetcherInterface
 * The interface for documents fetch strategy from Doofinder
 */
interface FetcherInterface
{
    const DOOFINDER_TRANSFORMER_ID = 'onlyid';
    const DOOFINDER_SEARCH_REQUEST_LIMIT = 100;
    const DOOFINDER_FACETS_REQUEST_LIMIT = 10;

    const KEY_AGGREGATIONS = 'aggregation';
    const KEY_IDS = 'ids';
    const KEY_TOTAL = 'total';

    /**
     * @param RequestInterface $request
     * @return array
     */
    public function fetch(RequestInterface $request);
}
