<?php
declare(strict_types=1);


namespace Doofinder\Feed\Observer\Product;

use Doofinder\Feed\Api\Data\ChangedItemInterface;

class CatalogProductDeleteAfterObserver extends AbstractChangedProductObserver
{
    private $operationType = ChangedItemInterface::OPERATION_TYPE_DELETE;

    protected function getOperationType(): string
    {
        return $this->operationType;
    }

    protected function setOperationType(string $operationType)
    {
    }
}
