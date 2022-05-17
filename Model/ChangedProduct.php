<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Doofinder\Feed\Model\ResourceModel\ChangedProduct as ChangedProductResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ChangedProduct extends AbstractModel implements ChangedProductInterface, IdentityInterface
{
    const CACHE_TAG = 'doofinder_feed_changed_product';

    /**
     * ChangedProduct constructor.
     * @return void
     */
    public function _construct()
    {
        $this->_init(ChangedProductResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getProductId(): ?int
    {
        $id = $this->_getData(self::PRODUCT_ID);

        return $id === null ? null : (int)$id;
    }

    /**
     * @inheritDoc
     */
    public function setProductId(int $id): ChangedProductInterface
    {
        return $this->setData(self::PRODUCT_ID, $id);
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
    public function setStoreId(int $id): ChangedProductInterface
    {
        return $this->setData(self::STORE_ID, $id);
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
    public function setOperationType(string $operationType): ChangedProductInterface
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
