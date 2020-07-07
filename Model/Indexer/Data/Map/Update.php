<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map;

use Doofinder\Feed\Model\Indexer\Data\MapInterface;
use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;

/**
 * Class Update
 * The class responsible for providing products data to index
 */
class Update implements MapInterface
{
    /**
     * @var Update\Builder
     */
    private $builder;

    /**
     * @var array
     */
    private $fetchers;

    /**
     * Update constructor.
     * @param Update\Builder $builder
     * @param array $fetchers
     */
    public function __construct(
        Update\Builder $builder,
        array $fetchers
    ) {
        $this->builder = $builder;
        $this->fetchers = $fetchers;
    }

    /**
     * Notice: add sorting?
     * @return FetcherInterface[]
     */
    public function getFetchers()
    {
        return $this->fetchers;
    }

    /**
     * {@inheritDoc}
     * @param array $documentData
     * @param integer $storeId
     * @return array
     */
    public function map(array $documentData, $storeId)
    {
        $documents = [];
        $productIds = array_keys($documentData);

        foreach ($this->getFetchers() as $fetcher) {
            $fetcher->process($documentData, $storeId);
            foreach ($productIds as $productId) {
                if (!isset($documents[$productId])) {
                    $documents[$productId] = [];
                    $this->builder->addField('store_id', (string) $storeId);
                }

                $this->builder->addFields(
                    $fetcher->get($productId)
                );
                $documents[$productId] = array_merge_recursive($documents[$productId], $this->builder->build());
            }
            $fetcher->clear();
        }

        $documents = array_filter($documents, function ($document) {
            return isset($document['id']);
        });
        return $documents;
    }
}
