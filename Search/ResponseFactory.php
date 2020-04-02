<?php

namespace Doofinder\Feed\Search;

use Magento\Framework\Search\Response\QueryResponseFactory;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Search\Response\Aggregation;

/**
 * Class ResponseFactory
 * The class responsible for creating QueryResponse object
 */
class ResponseFactory
{
    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var AggregationFactory
     */
    private $aggregationFactory;

    /**
     * @var QueryResponseFactory
     */
    private $queryResponseFactory;

    /**
     * ResponseFactory constructor.
     * @param DocumentFactory $documentFactory
     * @param AggregationFactory $aggregationFactory
     * @param QueryResponseFactory $queryResponseFactory
     */
    public function __construct(
        DocumentFactory $documentFactory,
        AggregationFactory $aggregationFactory,
        QueryResponseFactory $queryResponseFactory
    ) {
        $this->documentFactory = $documentFactory;
        $this->aggregationFactory = $aggregationFactory;
        $this->queryResponseFactory = $queryResponseFactory;
    }

    /**
     * @param array $response
     * @return QueryResponse
     */
    public function create(array $response)
    {
        $documents = [];
        foreach ($response['documents'] as $rawDocument) {
            /** @var Document[] $documents */
            $documents[] = $this->documentFactory->create(
                $rawDocument
            );
        }

        /** @var Aggregation $aggregations */
        $aggregations = $this->aggregationFactory->create($response['aggregations']);
        $response = $this->queryResponseFactory->create([
            'documents' => $documents,
            'aggregations' => $aggregations,
            'total' => $response['total']
        ]);
        return $response;
    }
}
