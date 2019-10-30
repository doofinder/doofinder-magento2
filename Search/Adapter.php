<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Search\AdapterInterface;
use Doofinder\Feed\Helper\Search;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Class Adapter
 * The class responsible for handling search request to Doofinder
 */
class Adapter implements AdapterInterface
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Aggregation\Builder
     */
    private $aggregationBuilder;

    /**
     * @var Search
     */
    private $search;

    /**
     * @var Filters
     */
    private $filters;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var RequestAggregation
     */
    private $requestAggregation;

    /**
     * Adapter constructor.
     * @param ResponseFactory $responseFactory
     * @param Aggregation\Builder $aggregationBuilder
     * @param Search $search
     * @param Filters $filters
     * @param Cache $cache
     * @param RequestAggregation $requestAggregation
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Aggregation\Builder $aggregationBuilder,
        Search $search,
        Filters $filters,
        Cache $cache,
        RequestAggregation $requestAggregation
    ) {
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->search = $search;
        $this->filters = $filters;
        $this->cache = $cache;
        $this->requestAggregation = $requestAggregation;
    }

    /**
     * {@inheritDoc}
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        $query = $request->getQuery();
        $filters = $this->filters->get($request);

        $rawResponse = $this->getDocuments(
            $this->getQueryString($query),
            $filters
        );
        $this->cache->setResponse($rawResponse);
        $rawDocuments = $rawResponse;

        $buckets = $request->getAggregation();
        $aggregatesBuckets = $this->requestAggregation->get($buckets, $rawResponse);
        $rawResponse['aggregations'] = $aggregatesBuckets;
        $aggregations = $this->aggregationBuilder->build($request, $rawResponse);

        $response = [
            'documents' => $rawDocuments,
            'aggregations' => $aggregations,
            'total' => count($rawDocuments),
        ];
        return $this->responseFactory->create($response);
    }

    /**
     * Executes query and return raw response
     *
     * @param string $queryText
     * @param array $filters
     * @return array
     */
    private function getDocuments($queryText, array $filters = [])
    {
        $results = $this->search->performDoofinderSearch($queryText, $filters);
        $score = count($results);

        foreach ($results as &$item) {
            $item['_score'] = $score--;
        }

        return $results;
    }

    /**
     * Get query string
     *
     * @notice This may not be the right way
     *
     * @param QueryInterface $query
     * @return string
     */
    private function getQueryString(QueryInterface $query)
    {
        $should = $query->getShould();
        if (isset($should['search'])) {
            return $should['search']->getValue();
        }

        return '';
    }
}
