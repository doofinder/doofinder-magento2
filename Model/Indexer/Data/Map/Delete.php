<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Indexer\Data\Map;

use Doofinder\Feed\Api\Data\MapInterface;

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
    public function map(array $documents, int $scopeId): array
    {
        // phpcs:enable
        return array_map(function ($productId) {
            return ['id' => $productId];
        }, $documents);
    }
}
