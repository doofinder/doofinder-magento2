<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Doofinder\Feed\Search\Adapter\FetcherInterface;

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
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * Adapter constructor.
     * @param ResponseFactory $responseFactory
     * @param Aggregation\Builder $aggregationBuilder
     * @param FetcherInterface $fetcher
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Aggregation\Builder $aggregationBuilder,
        FetcherInterface $fetcher
    ) {
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->fetcher = $fetcher;
    }

    /**
     * {@inheritDoc}
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        $queryResults = $this->fetcher->fetch($request);
        $documents = $this->getDocuments($queryResults[FetcherInterface::KEY_IDS]);
        $aggregations = $this->aggregationBuilder->build($request, $queryResults);

        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
            'total' => $queryResults[FetcherInterface::KEY_TOTAL],
        ];
        return $this->responseFactory->create($response);
    }

    /**
     * @param array $rawResponse
     * @return mixed
     */
    private function getDocuments(array $rawResponse)
    {
        $score = count($rawResponse);

        foreach ($rawResponse as &$item) {
            $item['_score'] = $score--;
        }

        return $rawResponse;
    }
}
