<?php

namespace Doofinder\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

/**
 * The resource model of product change trace.
 */
class ChangedProduct extends AbstractDB
{
    /**
     * Holds the name of the table responsible for storing identities of deleted products.
     *
     * @var string TABLE_NAME
     */
    const TABLE_NAME = 'doofinder_feed_changed_product';

    /**
     * Changed product trace row's identity field name.
     *
     * @var string FIELD_ID
     */
    const FIELD_ID = 'entity_id';

    /**
     * Changed product's identity field name.
     *
     * @var string FIELD_PRODUCT_ID
     */
    const FIELD_PRODUCT_ID = 'product_entity_id';

    /**
     * Operation performed on Changed product field name.
     *
     * This can be either 'update', 'disable' or 'delete' as of now.
     *
     * @var string FIELD_OPERATION_TYPE
     */
    const FIELD_OPERATION_TYPE = 'operation_type';

    /**
     * Store view the change was issued on.
     *
     * @var string FIELD_STORE_CODE
     */
    const FIELD_STORE_CODE = 'store_code';

    /**
     * Tells that the product was updated in regular way.
     *
     * Changes, however, may influence the visibility of the product and then the OPERATION_DISABLE is being
     * set in `Doofinder\Feed\Observer\DelayedUpdates\RegisterChange` class.
     *
     * @var string OPERATION_UPDATE
     */
    const OPERATION_UPDATE = 'update';

    /**
     * Tells that the product was deleted completely.
     *
     * @var string OPERATION_DELETE
     */
    const OPERATION_DELETE = 'delete';

    /**
     * Initializes resource model.
     *
     * @return void
     *
     * @codingStandardsIgnoreStart Method has to be protected.
     */
    protected function _construct()
    {
        /** @codingStandardsIgnoreEnd */

        $this->_init(
            self::TABLE_NAME,
            'entity_id'
        );
    }
}
