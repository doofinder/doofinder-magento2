<?php

/**
 * @see \Magento\Framework\Search\Adapter\Mysql
 */

namespace Doofinder\Feed\Search;

/**
 * Search adapter
 */
class Adapter implements \Magento\Framework\Search\AdapterInterface
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $adapter;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage
     */
    private $temporaryStorage;

    /**
     * @var \Magento\Framework\Api\Search\DocumentFactory
     */
    private $documentFactory;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder
     */
    private $aggregationBuilder;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

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
        $this->adapter = $adapter;
        $this->responseFactory = $responseFactory;
        $this->temporaryStorage = $temporaryStorage;
        $this->documentFactory = $documentFactory;
        $this->attributeValueFactory = $attributeValFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->search = $search;
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Magento\Framework\Search\RequestInterface $request
     * @return \Magento\Framework\Search\Adapter\Mysql\Response
     * @codingStandardsIgnoreStart
     */
    public function query(\Magento\Framework\Search\RequestInterface $request)
    {
    // @codingStandardsIgnoreEnd
        $query = $request->getQuery();
        if (preg_match('/quick_?search_container/', $request->getName()) !== 1
            || !$this->isSearchValue($query)
        ) {
            // @codingStandardsIgnoreStart
            return $this->adapter->query($request);
            // @codingStandardsIgnoreEnd
        }

        /**
         * NOTICE Add cache magic here ?
         */
        $documents = $this->getDocuments($this->getQueryString($query));
        $table = $this->temporaryStorage->storeApiDocuments($this->createDocuments($documents));

        $aggregations = $this->aggregationBuilder->build($request, $table, $documents);
        $response = [
            'documents' => $documents,
            'aggregations' => $aggregations,
            'total' => count($documents),
        ];

        return $this->responseFactory->create($response);
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
        $this->search->performDoofinderSearch($queryText);

        // Fetch all results
        $results = array_unique($this->search->getAllResults()); // fix: prevent more than one same id
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
     * @param  array $documents
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    private function createDocuments(array $documents)
    {
        return array_map(function ($data) {
            $score = $this->attributeValueFactory->create();
            $score->setAttributeCode('score');
            $score->setValue($data['score']);

            $document = $this->documentFactory->create();
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
        if (isset($should['search'])) {
            return $should['search']->getValue();
        }

        return '';
    }

    /**
     * Check whether query string exists
     *
     * @param \Magento\Framework\Search\Request\QueryInterface $query
     * @return boolean
     */
    private function isSearchValue(\Magento\Framework\Search\Request\QueryInterface $query)
    {
        $should = $query->getShould();
        return isset($should['search']);
    }
}
