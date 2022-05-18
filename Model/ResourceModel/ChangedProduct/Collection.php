<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\ResourceModel\ChangedProduct;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Doofinder\Feed\Model\ChangedProduct as ChangedProductModel;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResourceModel;

/**
 * Collection of deleted product traces.
 */
class Collection extends AbstractCollection
{
    /**
     * Initializes resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ChangedProductModel::class,
            ChangedProductResourceModel::class
        );
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function filterDeleted(int $storeId): Collection
    {
        $this->addFieldToSelect(ChangedProductInterface::CHANGED_PRODUCT_ID)
            ->addFieldToSelect(ChangedProductInterface::PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductInterface::OPERATION_TYPE,
                ChangedProductInterface::OPERATION_TYPE_DELETE
            )
            ->addFieldToFilter(
                ChangedProductInterface::STORE_ID,
                $storeId
            );

        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function filterUpdated(int $storeId): Collection
    {
        $this->addFieldToSelect(ChangedProductInterface::CHANGED_PRODUCT_ID)
            ->addFieldToSelect(ChangedProductInterface::PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductInterface::OPERATION_TYPE,
                ChangedProductInterface::OPERATION_TYPE_UPDATE
            )
            ->addFieldToFilter(
                ChangedProductInterface::STORE_ID,
                $storeId
            );
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function filterCreated(int $storeId): Collection
    {
        $this->addFieldToSelect(ChangedProductInterface::CHANGED_PRODUCT_ID)
            ->addFieldToSelect(ChangedProductInterface::PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductInterface::OPERATION_TYPE,
                ChangedProductInterface::OPERATION_TYPE_CREATE
            )
            ->addFieldToFilter(
                ChangedProductInterface::STORE_ID,
                $storeId
            );
        return $this;
    }
}
