<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Indexer\Data\Map;

use Doofinder\Feed\Api\Data\MapInterface;
use Doofinder\Feed\Api\Data\FetcherInterface;

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
    public function getFetchers(): array
    {
        return $this->fetchers;
    }

    /**
     * {@inheritDoc}
     * @param array $documents
     * @param integer $scopeId
     * @return array
     */
    public function map(array $documents, int $scopeId): array
    {
        $docs = [];
        $productIds = array_keys($documents);
        foreach ($this->getFetchers() as $fetcher) {
            $fetcher->process($documents, $scopeId);
            foreach ($productIds as $productId) {
                if (!isset($docs[$productId])) {
                    $docs[$productId] = [];
                    $this->builder->addField('store_id', (string) $scopeId);
                }
                $this->builder->addFields(
                    $fetcher->get($productId)
                );
                $docs[$productId] = array_merge_recursive($docs[$productId], $this->builder->build());
            }
            $fetcher->clear();
        }

        return array_values($docs);
    }
}
