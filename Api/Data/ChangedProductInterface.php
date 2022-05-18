<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

interface ChangedProductInterface
{
    /**
     * Changed product trace row's identity field name.
     *
     * @var string CHANGED_PRODUCT_ID
     */
    const CHANGED_PRODUCT_ID = 'entity_id';

    /**
     * Changed product's identity field name.
     *
     * @var string PRODUCT_ID
     */
    const PRODUCT_ID = 'product_id';

    /**
     * Store view the change was issued on.
     *
     * @var string STORE_ID
     */
    const STORE_ID = 'store_id';

    /**
     * Operation performed on Changed product field name.
     *
     * This can be either 'update', 'disable' or 'delete' as of now.
     *
     * @var string OPERATION_TYPE
     */
    const OPERATION_TYPE = 'operation_type';

    /**
     * Tells that the product was updated in regular way.
     *
     * @var string OPERATION_TYPE_UPDATE
     */
    const OPERATION_TYPE_UPDATE = 'update';

    /**
     * Tells that the product was deleted completely.
     *
     * @var string OPERATION_TYPE_DELETE
     */
    const OPERATION_TYPE_DELETE = 'delete';

    /**
     * Tells that the product was created.
     *
     * @var string OPERATION_TYPE_CRATE
     */
    const OPERATION_TYPE_CREATE = 'create';

    /**
     * Appointment id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set trace id
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id);

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId(): ?int;

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setProductId(int $id): ChangedProductInterface;

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
    public function setStoreId(int $id): ChangedProductInterface;

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
    public function setOperationType(string $operationType): ChangedProductInterface;
}
