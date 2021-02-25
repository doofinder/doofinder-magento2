<?php

namespace Doofinder\Feed\Search\Adapter;

use Doofinder\Feed\Model\Api\Search;
use Magento\Framework\Search\RequestInterface;
use Doofinder\Feed\Search\Facets;
use Doofinder\Feed\Search\Filters;
use Psr\Log\LoggerInterface;

/**
 * Class AllFetcher
 * The class responsible for handling search request to Doofinder
 * This fetcher is fetching all product ids with all facets
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AllFetcher implements FetcherInterface
{
    /**
     * @var Search
     */
    private $search;

    /**
     * @var Filters
     */
    private $filters;

    /**
     * @var Facets
     */
    private $facets;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AllFetcher constructor.
     * @param Search $search
     * @param Filters $filters
     * @param Facets $facets
     * @param LoggerInterface $logger
     */
    public function __construct(
        Search $search,
        Filters $filters,
        Facets $facets,
        LoggerInterface $logger
    ) {
        $this->search = $search;
        $this->filters = $filters;
        $this->facets = $facets;
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    public function fetch(RequestInterface $request)
    {
        // prepare basic data
        $filters = $this->filters->get($request);
        $buckets = $request->getAggregation();

        $productsLimit = self::DOOFINDER_SEARCH_REQUEST_LIMIT;
        $facetsLimit = self::DOOFINDER_FACETS_REQUEST_LIMIT;

        $filters['rpp'] = $productsLimit;
        $filters['transformer'] = self::DOOFINDER_TRANSFORMER_ID;

        // translate Magento buckets into Doofinder facets
        $preparedBuckets = $this->facets->get($buckets);

        // calculate needed facets requests based on the number of needed buckets
        $facetsRequests = ceil(count($preparedBuckets) / $facetsLimit);
        $facetsRequests--;

        // add facets to request and remove from prepared buckets
        $filters['facets'] = array_slice($preparedBuckets, 0, $facetsLimit - 1);
        array_splice($preparedBuckets, 0, $facetsLimit - 1);
        try {
            // make the first request
            $results = $this->search->execute($filters);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            $results = null;
        }

        if (!$results) {
            return [
                self::KEY_AGGREGATIONS => [],
                self::KEY_IDS => [],
                self::KEY_TOTAL => 0
            ];
        }

        // calculate needed requests based on the number of available products from API
        $resultsRequests = ceil((int) $results->getProperty('total') / $productsLimit);
        $resultsRequests--;

        // init array with data from API
        $dooFacets = $results->getFacets();
        $dooProducts = $results->getResults();

        // choose strategy
        $strategy = $facetsRequests > $resultsRequests ? 'facets' : 'pages';

        if ($strategy == 'pages') {
            // pages strategy is making as many request are needed to fetch all product ids from API
            for ($i = 1; $i <= $resultsRequests; $i++) {
                // in case when there are no more facets in API, don't ask about them
                unset($filters['facets']);
                if ($facetsRequests > 0) {
                    $filters['facets'] = array_slice($preparedBuckets, 0, $facetsLimit - 1);
                    array_splice($preparedBuckets, 0, $facetsLimit - 1);
                    $facetsRequests--;
                }
                // increase page and make the request
                $filters['page'] = $i + 1;
                try {
                    $results = $this->search->execute($filters);
                } catch (\Exception $exception) {
                    $this->logger->error($exception);
                    continue;
                }
                // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                // merge response into arrays
                $dooFacets = array_merge($dooFacets, $results->getFacets());
                $dooProducts = array_merge($dooProducts, $results->getResults());
                // phpcs:enable
            }
        } elseif ($strategy == 'facets') {
            // facet strategy is making as many request are needed to fetch all facets from API
            for ($i = 1; $i <= $facetsRequests; $i++) {
                unset($filters['facets']);
                $filters['facets'] = array_slice($preparedBuckets, 0, $facetsLimit - 1);
                array_splice($preparedBuckets, 0, $facetsLimit - 1);

                // in case when there are no more products in API, don't ask about them
                // in other case, API will return 4xx error
                if ($resultsRequests > 0) {
                    $filters['page'] = $i + 1;
                }

                try {
                    $results = $this->search->execute($filters);
                } catch (\Exception $exception) {
                    $this->logger->error($exception);
                    continue;
                }
                // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                // merge facets into array
                $dooFacets = array_merge($dooFacets, $results->getFacets());
                // phpcs:enable

                // merge products only if they're from new page
                // in other case array with product ids will contain duplicates
                if ($resultsRequests > 0) {
                    // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                    $dooProducts = array_merge($dooProducts, $results->getResults());
                    // phpcs:enable
                }
                $resultsRequests--;
            }
        }

        // make sure that there is not duplicated product ids
        $dooProducts = array_unique($dooProducts, SORT_REGULAR);

        return [
            self::KEY_AGGREGATIONS => $dooFacets,
            self::KEY_IDS => $dooProducts,
            self::KEY_TOTAL => $results->getProperty('total')
        ];
    }
}
