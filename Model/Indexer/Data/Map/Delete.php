<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map;

use Doofinder\Feed\Model\Indexer\Data\MapInterface;

/**
 * Class Delete
 * The class responsible for providing products that should be deleted in the index
 */
class Delete implements MapInterface
{
    /**
     * {@inheritDoc}
     * @param array $documents
     * @param integer $scopeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function map(array $documents, $scopeId)
    {
        return array_map(function ($productId) {
            return ['id' => $productId];
        }, $documents);
    }
}
