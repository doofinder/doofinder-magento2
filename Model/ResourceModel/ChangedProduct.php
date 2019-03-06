<?php

namespace Doofinder\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

/**
 * Deleted product trace resource model.
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
     * This can be either 'update' or 'delete' as of now.
     *
     * @var string FIELD_OPERATION_TYPE
     */
    const FIELD_OPERATION_TYPE = 'operation_type';

    /**
     * Changed product's update trace operation type.
     *
     * @var string OPERATION_UPDATE
     */
    const OPERATION_UPDATE = 'update';

    /**
     * Changed product's delete trace operation type.
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
