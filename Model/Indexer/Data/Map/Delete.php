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
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterfaceAfterLastUsed
     */
    public function map(array $documents, $scopeId)
    {
        // fix empty array in array
        if (isset($documents[0]) && empty($documents[0])) {
            return [];
        }
        // phpcs:enable
        return array_values(array_map(function ($productId) {
            return ['id' => $productId];
        }, $documents));
        // phpcs:disable
    }
}
