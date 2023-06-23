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
abstract class Collection extends AbstractCollection
{
    private int $itemType;

    /**
     * Initializes resource model.
     *
     * @return void
     */
    protected function _construct(int $itemType)
    {
        $this->itemType = $itemType;
        $this->_init(
            ChangedItemModel::class,
            ChangedItemResourceModel::class
        );
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function filterDeleted(int $storeId): Collection
    {
        $this->addFieldToSelect(ChangedItemInterface::CHANGED_ITEM_ID)
            ->addFieldToSelect(ChangedItemInterface::ITEM_ID)
            ->addFieldToFilter(
                ChangedItemInterface::OPERATION_TYPE,
                ChangedItemInterface::OPERATION_TYPE_DELETE
            )
            ->addFieldToFilter(
                ChangedItemInterface::TYPE,
                $this->itemType
            )
            ->addFieldToFilter(
                ChangedItemInterface::STORE_ID,
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
        $this->addFieldToSelect(ChangedItemInterface::CHANGED_ITEM_ID)
            ->addFieldToSelect(ChangedItemInterface::ITEM_ID)
            ->addFieldToFilter(
                ChangedItemInterface::OPERATION_TYPE,
                ChangedItemInterface::OPERATION_TYPE_UPDATE
            )

            ->addFieldToFilter(
                ChangedItemInterface::TYPE,
                $this->itemType
            )
            ->addFieldToFilter(
                ChangedItemInterface::STORE_ID,
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
        $this->addFieldToSelect(ChangedItemInterface::CHANGED_ITEM_ID)
            ->addFieldToSelect(ChangedItemInterface::ITEM_ID)
            ->addFieldToFilter(
                ChangedItemInterface::OPERATION_TYPE,
                ChangedItemInterface::OPERATION_TYPE_CREATE
            )
            ->addFieldToFilter(
                ChangedItemInterface::TYPE,
                $this->itemType
            )
            ->addFieldToFilter(
                ChangedItemInterface::STORE_ID,
                $storeId
            );
        return $this;
    }
}
