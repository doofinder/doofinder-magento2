<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Inventory helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Inventory extends AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectmanager
     * @param Manager $moduleManager
     * @param StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectmanager,
        Manager $moduleManager,
        StoreManagerInterface $storeManager
    ) {
        $this->_objectManager = $objectmanager;
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get quantity and stock status
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     */
    public function getQuantityAndStockStatus(ProductModel $product, ?int $stockId = null)
    {
        return $this->isMsiActive() ?
            $this->getQuantityAndStockStatusWithMSIMessage($product, $stockId) :
            $this->getQuantityAndStockStatusWithoutMSIMessage($product);
    }

    /**
     * Get quantity and product availability
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     */
    public function getQuantityAndAvailability(ProductModel $product, ?int $stockId = null)
    {
        return $this->isMsiActive() ?
            $this->getQuantityAndStockStatusWithMSI($product, $stockId) :
            $this->getQuantityAndStockStatusWithoutMSI($product);
    }

    /**
     * Get product availability
     *
     * @param ProductModel $product
     * @param int|null $stockId
     * @return string
     */
    public function getProductAvailability(ProductModel $product, ?int $stockId = null)
    {
        return $this->isMsiActive() ?
            $this->getProductAvailabilityWithMSI($product, $stockId) :
            $this->getProductAvailabilityWithoutMSI($product);
    }

    /**
     * Get stockId related with the given store.
     * Note: Each website is related only with one stock but one stock can be used by several websites.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStockIdByStore(int $storeId): ?int 
    {
        return $this->isMsiActive() ?
            $this->getStockIdByStoreWithMSI($storeId) :
            null;
        
    }

    /**
     * Get quantity and stock status for environments with MSI dependency
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return array
     */
    private function getQuantityAndStockStatusWithMSI(ProductModel $product, ?int $stockId = null)
    {
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);
        $qty = $stockItemData[\Magento\InventorySalesApi\Model\GetStockItemDataInterface::QUANTITY];
        $availability = $stockItemData[\Magento\InventorySalesApi\Model\GetStockItemDataInterface::IS_SALABLE];

        return [$qty, $availability];
    }

    /**
     * Get quantity and stock status for environments with MSI dependency
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     */
    private function getQuantityAndStockStatusWithMSIMessage(ProductModel $product, ?int $stockId = null)
    {
        $qtyAndAvailability = $this->getQuantityAndStockStatusWithMSI($product, $stockId);
        $qtyAndAvailability[1] = $qtyAndAvailability[1] ? $this->getInStockLabel(): $this->getOutOfStockLabel();

        return implode(' - ', array_filter($qtyAndAvailability, function ($item) {
            return $item !== null;
        }));
    }

    /**
     * Get product availability for environments with MSI dependency
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     */
    private function getProductAvailabilityWithMSI(ProductModel $product, ?int $stockId = null)
    {
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);

        return $stockItemData[\Magento\InventorySalesApi\Model\GetStockItemDataInterface::IS_SALABLE]
            ? $this->getInStockLabel()
            : $this->getOutOfStockLabel();
    }

    /**
     * @param string $sku
     * @param int|null $stockId
     *
     * @return array
     */
    private function getStockItemData(string $sku, ?int $stockId = null)
    {
        $defaultStockProvider = $this->_objectManager->create(\Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface::class);
        $getStockItemData = $this->_objectManager->create(\Magento\InventorySalesApi\Model\GetStockItemDataInterface::class);
        
        $stockId = $stockId ?? $defaultStockProvider->getId();
        $stockItemData = $getStockItemData->execute($sku, $stockId);

        return [
            \Magento\InventorySalesApi\Model\GetStockItemDataInterface::QUANTITY => $stockItemData[\Magento\InventorySalesApi\Model\GetStockItemDataInterface::QUANTITY] ?? 0,
            \Magento\InventorySalesApi\Model\GetStockItemDataInterface::IS_SALABLE => (bool)($stockItemData[\Magento\InventorySalesApi\Model\GetStockItemDataInterface::IS_SALABLE] ?? false)
        ];
    }

    /**
     * Get quantity and stock status for environments without MSI dependency
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return array
     */
    private function getQuantityAndStockStatusWithoutMSI(ProductModel $product)
    {
        $qty = $this->getStockItem($product->getId())->getQty();
        $availability = $this->getStockItem($product->getId())->getIsInStock();

        return [$qty, $availability];
    }

    /**
     * Get quantity and stock status for environments without MSI dependency
     *
     * @param ProductModel $product
     * @param int|null $stockId
     *
     * @return string
     */
    private function getQuantityAndStockStatusWithoutMSIMessage(ProductModel $product)
    {
        $qtyAndAvailability = $this->getQuantityAndStockStatusWithoutMSI($product);
        $qtyAndAvailability[1] = $qtyAndAvailability[1] ? $this->getInStockLabel(): $this->getOutOfStockLabel();

        return implode(' - ', array_filter($qtyAndAvailability, function ($item) {
            return $item !== null;
        }));
    }

    /**
     * Get product availability for environments without MSI dependency
     *
     * @param ProductModel $product
     * @return string
     */
    private function getProductAvailabilityWithoutMSI(ProductModel $product)
    {
        if ($this->getStockItem($product->getId())->getIsInStock()) {
            return $this->getInStockLabel();
        }

        return $this->getOutOfStockLabel();
    }

    /**
     * Get stock item
     *
     * @param integer $productId
     * @return \Magento\CatalogInventory\Model\Stock\Item
     */
    private function getStockItem($productId)
    {
        $stockRegistry = $this->_objectManager->create(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
        return $stockRegistry->getStockItem($productId);
    }

    /**
     * Get product 'out of stock' label
     *
     * @return string
     */
    private function getOutOfStockLabel(): string
    {
        return 'out of stock';
    }

    /**
     * Get product 'in stock' label
     *
     * @return string
     */
    private function getInStockLabel(): string
    {
        return 'in stock';
    }

    /**
     * Function to get the stockId related with the given store / website
     * 
     * @return int
     */
    private function getStockIdByStoreWithMSI(int $storeId): ?int
    {
        $getAssignedStockIdForWebsite = $this->_objectManager->create(\Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite::class);
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        return $getAssignedStockIdForWebsite->execute($websiteCode);
    }

    /**
     * Function to detect if MSI module is active or not.
     * For the moment is enugh checking those two dependencies because we're working only with those.
     * 
     * @return bool
     */
    private function isMsiActive(): bool
    {
        return ($this->moduleManager->isEnabled('Magento_InventorySalesApi') && $this->moduleManager->isEnabled('Magento_InventoryCatalogApi'));
    }
}
