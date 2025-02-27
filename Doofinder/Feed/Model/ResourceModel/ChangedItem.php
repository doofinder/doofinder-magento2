<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * The resource model of product change trace.
 */
class ChangedItem extends AbstractDb
{
    /**
     * Holds the name of the table responsible for storing identities of deleted products.
     *
     * @var string TABLE_NAME
     */
    public const TABLE_NAME = 'doofinder_feed_changed_item';

    public const ENTITY_ID = 'entity_id';

    /**
     * Initializes resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ENTITY_ID);
    }
}
