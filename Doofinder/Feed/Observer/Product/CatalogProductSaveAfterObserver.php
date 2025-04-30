<?php
declare(strict_types=1);


namespace Doofinder\Feed\Observer\Product;

use Doofinder\Feed\Api\Data\ChangedItemInterface;

class CatalogProductSaveAfterObserver extends AbstractChangedProductObserver
{
    /**
     * Type of operation, can be create, update or delete.
     *
     * @var string
     */
    private $operationType = ChangedItemInterface::OPERATION_TYPE_UPDATE;

    /**
     * Gets the operation type.
     *
     * @return string
     */
    protected function getOperationType(): string
    {
        return $this->operationType;
    }

    /**
     * Sets the operation type.
     *
     * @param string $operationType
     * @return void
     */
    protected function setOperationType(string $operationType)
    {
        $this->operationType = $operationType;
    }
}
