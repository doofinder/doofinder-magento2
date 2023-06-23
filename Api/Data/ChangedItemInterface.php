<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

interface ChangedItemInterface
{
    /**
     * Changed item trace row's identity field name.
     *
     * @var string CHANGED_ITEM_ID
     */
    const CHANGED_ITEM_ID = 'entity_id';
    
    /**
     * Changed item trace row's identity field name.
     *
     * @var string CHANGED_ITEM_ID
     */
    const ITEM_ID = 'item_id';

    /**
     * Store view the change was issued on.
     *
     * @var string STORE_ID
     */
    const STORE_ID = 'store_id';

    /**
     * Item type.
     *
     * @var string ITEM_TYPE
     */
    const ITEM_TYPE = 'item_type';

    /**
     * Operation performed on Changed item field name.
     *
     * This can be either 'update', 'disable' or 'delete' as of now.
     *
     * @var string OPERATION_TYPE
     */
    const OPERATION_TYPE = 'operation_type';

    /**
     * Tells that the item was updated in regular way.
     *
     * @var string OPERATION_TYPE_UPDATE
     */
    const OPERATION_TYPE_UPDATE = 'update';

    /**
     * Tells that the item was deleted completely.
     *
     * @var string OPERATION_TYPE_DELETE
     */
    const OPERATION_TYPE_DELETE = 'delete';

    /**
     * Tells that the item was created.
     *
     * @var string OPERATION_TYPE_CRATE
     */
    const OPERATION_TYPE_CREATE = 'create';

    /**
     * Get item id
     *
     * @return int|null
     */
    public function getItemId(): ?int;

    /**
     * Set item id
     *
     * @param int $id
     * @return $this
     */
    public function setItemId(int $id): ChangedItemInterface;

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * Set store id
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId(int $id): ChangedItemInterface;

    /**
     * Get item type
     *
     * @return int|null
     */
    public function getItemType(): ?int;

    /**
     * Set item type
     *
     * @param int $type
     * @return $this
     */
    public function setItemType(int $type): ChangedItemInterface;

    /**
     * Get store id
     *
     * @return string|null
     */
    public function getOperationType(): ?string;

    /**
     * Set store id
     *
     * @param string $operationType
     * @return $this
     */
    public function setOperationType(string $operationType): ChangedItemInterface;
}
