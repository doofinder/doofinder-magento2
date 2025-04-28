<?php
declare(strict_types=1);


namespace Doofinder\Feed\Observer\Product;

use Doofinder\Feed\Api\Data\ChangedItemInterface;

class CatalogProductDeleteAfterObserver extends AbstractChangedProductObserver
{
    /** @var string */
    private $operationType = ChangedItemInterface::OPERATION_TYPE_DELETE;

    /**
     * Gets operation type
     *
     * @return string
     */
    protected function getOperationType(): string
    {
        return $this->operationType;
    }

    /**
     * Sets the operation type
     *
     * @param string $operationType
     * @return void
     */
    protected function setOperationType(string $operationType)
    {
        $this->operationType = $operationType;
    }
}
