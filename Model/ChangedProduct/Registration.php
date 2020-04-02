<?php

namespace Doofinder\Feed\Model\ChangedProduct;

use Doofinder\Feed\Model\ChangedProductFactory;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResource;

/**
 * Class Registration
 * The class responsible for saving information about altered products
 */
class Registration
{
    /**
     * @var ChangedProductFactory
     */
    private $changedFactory;

    /**
     * @var ChangedProductResource
     */
    private $changedResource;

    /**
     * Registration constructor.
     * @param ChangedProductFactory $changedFactory
     * @param ChangedProductResource $changedResource
     */
    public function __construct(
        ChangedProductFactory $changedFactory,
        ChangedProductResource $changedResource
    ) {
        $this->changedFactory = $changedFactory;
        $this->changedResource = $changedResource;
    }

    /**
     * @param integer $productId
     * @param string $storeCode
     * @return void
     */
    public function registerUpdate($productId, $storeCode)
    {
        $this->leaveUpdateTrace($productId, $storeCode);
    }

    /**
     * @param integer $productId
     * @param string $storeCode
     * @return void
     */
    public function registerDelete($productId, $storeCode)
    {
        $this->leaveDeleteTrace($productId, $storeCode);
    }

    /**
     * Leaves an update trace for a product change.
     *
     * This method invokes a `leaveTrace()` method with appropriate
     * operation value as its parameter.
     *
     * @param integer $productId
     * @param string $storeCode
     *
     * @return void
     */
    private function leaveUpdateTrace($productId, $storeCode)
    {
        $this->leaveTrace(
            $productId,
            $storeCode,
            ChangedProductResource::OPERATION_UPDATE
        );
    }

    /**
     * Leaves a delete trace for a product change.
     *
     * This method invokes a `leaveTrace()` method with appropriate
     * operation value as its parameter.
     *
     * @param integer $productId
     * @param string $storeCode
     *
     * @return void
     */
    private function leaveDeleteTrace($productId, $storeCode)
    {
        $this->leaveTrace(
            $productId,
            $storeCode,
            ChangedProductResource::OPERATION_DELETE
        );
    }

    /**
     * Stores a trace of a product change.
     *
     * If there already exists a trace of the product change in a given store view,
     * then it's retrieved and updated with an appropriate operation value.
     *
     * If there is no trace for the product in given store view, a new one is created.
     *
     * @param integer $productId
     * @param string $storeCode
     * @param string $operation
     *
     * @return void
     */
    private function leaveTrace($productId, $storeCode, $operation)
    {
        $trace = $this->changedFactory->create();
        $this->changedResource->loadChanged(
            $trace,
            $productId,
            $storeCode,
            $operation
        );

        $trace->setProductEntityId($productId)
            ->setStoreCode($storeCode)
            ->setOperationType($operation);

        $this->changedResource->save($trace);
    }
}
