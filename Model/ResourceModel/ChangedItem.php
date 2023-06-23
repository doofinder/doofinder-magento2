<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDB;

/**
 * The resource model of product change trace.
 */
class ChangedItem extends AbstractDB
{
    /**
     * Holds the name of the table responsible for storing identities of deleted products.
     *
     * @var string TABLE_NAME
     */
    const TABLE_NAME = 'doofinder_feed_changed_item';

    /**
     * Initializes resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            self::TABLE_NAME,
            'entity_id'
        );
    }
}
