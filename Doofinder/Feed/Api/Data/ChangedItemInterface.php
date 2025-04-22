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
    public const CHANGED_ITEM_ID = 'entity_id';
    
    /**
     * Changed item trace row's identity field name.
     *
     * @var string CHANGED_ITEM_ID
     */
    public const ITEM_ID = 'item_id';

    /**
     * Store view the change was issued on.
     *
     * @var string STORE_ID
     */
    public const STORE_ID = 'store_id';

    /**
     * The type of item that represents inside the table.
     *
     * @var string ITEM_TYPE
     */
    public const ITEM_TYPE = 'item_type';

    /**
     * Operation performed on Changed item field name.
     *
     * This can be either 'update', 'disable' or 'delete' as of now.
     *
     * @var string OPERATION_TYPE
     */
    public const OPERATION_TYPE = 'operation_type';

    /**
     * Tells that the item was updated in regular way.
     *
     * @var string OPERATION_TYPE_UPDATE
     */
    public const OPERATION_TYPE_UPDATE = 'update';

    /**
     * Tells that the item was deleted completely.
     *
     * @var string OPERATION_TYPE_DELETE
     */
    public const OPERATION_TYPE_DELETE = 'delete';

    /**
     * Tells that the item was created.
     *
     * @var string OPERATION_TYPE_CRATE
     */
    public const OPERATION_TYPE_CREATE = 'create';

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
     * Gets the operation type
     *
     * @return string|null
     */
    public function getOperationType(): ?string;

    /**
     * Sets the operation type
     *
     * @param string $operationType
     * @return $this
     */
    public function setOperationType(string $operationType): ChangedItemInterface;
}
