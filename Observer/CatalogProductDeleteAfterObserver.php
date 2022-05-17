<?php
declare(strict_types=1);


namespace Doofinder\Feed\Observer;

use Doofinder\Feed\Api\Data\ChangedProductInterface;

class CatalogProductDeleteAfterObserver extends AbstractChangedProductObserver
{
    
    protected function getOperationType(): string
    {
        return ChangedProductInterface::OPERATION_TYPE_DELETE;
    }
    protected function setOperationType(string $operationType) {}
}
