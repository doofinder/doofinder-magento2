<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\ResourceModel;

use Doofinder\Feed\Api\Data\ChangedProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDB;
use Magento\Framework\Model\AbstractModel;

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
