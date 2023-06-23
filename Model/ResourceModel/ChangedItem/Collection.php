<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\ResourceModel\ChangedItem;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Doofinder\Feed\Model\ChangedItem as ChangedItemModel;
use Doofinder\Feed\Model\ResourceModel\ChangedItem as ChangedItemResourceModel;

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
            ChangedItemModel::class,
            ChangedItemResourceModel::class
        );
    }

    /**
     * @param int $storeId
     * @param int $itemType
     * @return $this
     */
    public function filterDeleted(int $storeId, int $itemType): Collection
    {
        $this->addFieldToSelect(ChangedItemInterface::CHANGED_ITEM_ID)
            ->addFieldToSelect(ChangedItemInterface::ITEM_ID)
            ->addFieldToFilter(
                ChangedItemInterface::OPERATION_TYPE,
                ChangedItemInterface::OPERATION_TYPE_DELETE
            )
            ->addFieldToFilter(
                ChangedItemInterface::ITEM_TYPE,
                $itemType
            )
            ->addFieldToFilter(
                ChangedItemInterface::STORE_ID,
                $storeId
            );

        return $this;
    }

    /**
     * @param int $storeId
     * @param int $itemType
     * @return $this
     */
    public function filterUpdated(int $storeId, int $itemType): Collection
    {
        $this->addFieldToSelect(ChangedItemInterface::CHANGED_ITEM_ID)
            ->addFieldToSelect(ChangedItemInterface::ITEM_ID)
            ->addFieldToFilter(
                ChangedItemInterface::OPERATION_TYPE,
                ChangedItemInterface::OPERATION_TYPE_UPDATE
            )

            ->addFieldToFilter(
                ChangedItemInterface::ITEM_TYPE,
                $itemType
            )
            ->addFieldToFilter(
                ChangedItemInterface::STORE_ID,
                $storeId
            );
        return $this;
    }

    /**
     * @param int $storeId
     * @param int $itemType
     * @return $this
     */
    public function filterCreated(int $storeId, int $itemType): Collection
    {
        $this->addFieldToSelect(ChangedItemInterface::CHANGED_ITEM_ID)
            ->addFieldToSelect(ChangedItemInterface::ITEM_ID)
            ->addFieldToFilter(
                ChangedItemInterface::OPERATION_TYPE,
                ChangedItemInterface::OPERATION_TYPE_CREATE
            )
            ->addFieldToFilter(
                ChangedItemInterface::ITEM_TYPE,
                $itemType
            )
            ->addFieldToFilter(
                ChangedItemInterface::STORE_ID,
                $storeId
            );
        return $this;
    }
}
