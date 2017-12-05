<?php

namespace Doofinder\Feed\Search;

/**
 * Adapter class
 *
 * @see \Magento\Framework\Search\Adapter\Mysql
 * @package Doofinder\Feed\Search
 */
class Adapter implements \Magento\Framework\Search\AdapterInterface
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $_adapter;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    private $_responseFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage
     */
    private $_temporaryStorage;

    /**
     * @var \Magento\Framework\Api\Search\DocumentFactory
     */
    private $_documentFactory;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory
     */
    private $_attributeValueFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    private $_aggregationBuilder;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_search;

    /**
     * @param \Magento\Framework\Search\Adapter\Mysql\Adapter $adapter
     * @param \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage $temporaryStorage
     * @param \Magento\Framework\Api\Search\DocumentFactory $documentFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $attributeValFactory
     * @param \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder $aggregationBuilder
     * @param \Doofinder\Feed\Helper\Search $search
     */
    public function __construct(
        \Magento\Framework\Search\Adapter\Mysql\Adapter $adapter,
        \Magento\Framework\Search\Adapter\Mysql\ResponseFactory $responseFactory,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage $temporaryStorage,
        \Magento\Framework\Api\Search\DocumentFactory $documentFactory,
        \Magento\Framework\Api\AttributeValueFactory $attributeValFactory,
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder $aggregationBuilder,
        \Doofinder\Feed\Helper\Search $search
    ) {
        $this->_adapter = $adapter;
        $this->_responseFactory = $responseFactory;
        $this->_temporaryStorage = $temporaryStorage;
        $this->_documentFactory = $documentFactory;
        $this->_attributeValueFactory = $attributeValFactory;
        $this->_aggregationBuilder = $aggregationBuilder;
        $this->_search = $search;
    }

    /**
     * {@inheritdoc}
     * @codingStandardsIgnoreStart
     */
    public function query(\Magento\Framework\Search\RequestInterface $request)
    {
    // @codingStandardsIgnoreEnd
        if ($request->getName() != 'quick_search_container') {
            // @codingStandardsIgnoreStart
            return $this->_adapter->query($request);
            // @codingStandardsIgnoreEnd
        }

        /**
         * NOTICE Add cache magic here ?
         */
        $documents = $this->getDocuments($this->getQueryString($request->getQuery()));
        $table = $this->_temporaryStorage->storeApiDocuments($this->createDocuments($documents));

        $aggregations = [];
        $aggregations = $this->_aggregationBuilder->build($request, $table, $documents);
        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
        ];

        return $this->_responseFactory->create($response);
    }

    /**
     * Executes query and return raw response
     *
     * @param string $queryText
     * @return array
     */
    private function getDocuments($queryText)
    {
        // Execute initial search
        $this->_search->performDoofinderSearch($queryText);

        // Fetch all results
        $results = $this->_search->getAllResults();
        $score = count($results);

        $documents = [];
        foreach ($results as $item) {
            $documents[] = [
                'entity_id' => $item,
                'score' => $score--,
            ];
        }

        return $documents;
    }

    /**
     * Create documents
     *
     * @param array
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    private function createDocuments($documents)
    {
        return array_map(function ($data) {
            $score = $this->_attributeValueFactory->create();
            $score->setAttributeCode('score');
            $score->setValue($data['score']);

            $document = $this->_documentFactory->create();
            $document->setId($data['entity_id']);
            $document->setCustomAttribute('score', $score);

            return $document;
        }, $documents);
    }

    /**
     * Get query string
     *
     * @notice This may not be the right way
     *
     * @param \Magento\Framework\Search\Request\QueryInterface $query
     * @return string
     */
    private function getQueryString(\Magento\Framework\Search\Request\QueryInterface $query)
    {
        $should = $query->getShould();
        return $should['search']->getValue();
    }
}
