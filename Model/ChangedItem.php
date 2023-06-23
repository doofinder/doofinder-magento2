<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedItem as ChangedItemResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ChangedItem extends AbstractModel implements ChangedItemInterface, IdentityInterface
{
    const CACHE_TAG = 'doofinder_feed_changed_item';

    /**
     * ChangedItem constructor.
     * @return void
     */
    public function _construct()
    {
        $this->_init(ChangedItemResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getItemId(): ?int
    {
        $id = $this->_getData(self::ITEM_ID);

        return $id === null ? null : (int)$id;
    }

    /**
     * @inheritDoc
     */
    public function setItemId(int $id): ChangedItemInterface
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): ?int
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(int $id): ChangedItemInterface
    {
        return $this->setData(self::STORE_ID, $id);
    }
    
    /**
     * @inheritDoc
     */
    public function getItemType(): ?int
    {
        return $this->_getData(self::ITEM_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setItemType(int $type): ChangedItemInterface
    {
        return $this->setData(self::ITEM_TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getOperationType(): ?string
    {
        return $this->_getData(self::OPERATION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setOperationType(string $operationType): ChangedItemInterface
    {
        return $this->setData(self::OPERATION_TYPE, $operationType);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
