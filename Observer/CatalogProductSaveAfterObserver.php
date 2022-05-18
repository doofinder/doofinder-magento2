<?php
declare(strict_types=1);


namespace Doofinder\Feed\Observer;

use Doofinder\Feed\Api\Data\ChangedProductInterface;

class CatalogProductSaveAfterObserver extends AbstractChangedProductObserver
{
    private $operationType = ChangedProductInterface::OPERATION_TYPE_UPDATE;

    protected function getOperationType(): string
    {
        return $this->operationType;
    }

    protected function setOperationType(string $operationType)
    {
        $this->operationType = $operationType;
    }
}
