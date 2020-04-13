<?php

namespace Doofinder\Feed\Model\ResourceModel\ChangedProduct;

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
     * @param string $storeCode
     * @return $this
     */
    public function filterDeleted($storeCode)
    {
        $this->addFieldToSelect(ChangedProductResourceModel::FIELD_ID)
            ->addFieldToSelect(ChangedProductResourceModel::FIELD_PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductResourceModel::FIELD_OPERATION_TYPE,
                ChangedProductResourceModel::OPERATION_DELETE
            )
            ->addFieldToFilter(
                ChangedProductResourceModel::FIELD_STORE_CODE,
                $storeCode
            );

        return $this;
    }

    /**
     * @param string $storeCode
     * @return $this
     */
    public function filterUpdated($storeCode)
    {
        $this->addFieldToSelect(ChangedProductResourceModel::FIELD_ID)
            ->addFieldToSelect(ChangedProductResourceModel::FIELD_PRODUCT_ID)
            ->addFieldToFilter(
                ChangedProductResourceModel::FIELD_OPERATION_TYPE,
                ChangedProductResourceModel::OPERATION_UPDATE
            )
            ->addFieldToFilter(
                ChangedProductResourceModel::FIELD_STORE_CODE,
                $storeCode
            );
        return $this;
    }
}
