<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Doofinder\Feed\Errors\DoofinderFeedException;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
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
     * @param                                          Context                $context
     * @param                                          ObjectManagerInterface $objectmanager
     * @param                                          Manager                $moduleManager
     * @param                                          StoreManagerInterface  $storeManager
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
     * Get quantity and product availability
     *
     * @param ProductModel $product
     * @param int|null     $stockId
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
     * @param  ProductModel $product
     * @param  int|null     $stockId
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
     *
     * Note: Each website is related only with one stock but one stock can be used by several websites.
     *
     * @param  int|null $storeId
     * @return string
     */
    public function getStockIdByStore(int $storeId): ?int
    {
        return $this->isMsiActive() ?
            $this->getStockIdByStoreWithMSI($storeId) :
            null;
    }

    /**
     * Get the maximum order quantity for a product.
     *
     * @param ProductModel $product
     * @param ?int         $stockId
     *
     * @return float|null
     */
    public function getMaximumOrderQuantity(ProductModel $product, ?int $stockId): float|null
    {
        if ($this->isMsiActive()) {
            $defaultStockProvider = $this->_objectManager->create(DefaultStockProviderInterface::class);
            $stockId = $stockId ?? $defaultStockProvider->getId();

            $getConfig = $this->_objectManager->create(GetStockItemConfigurationInterface::class);
            $config = $getConfig->execute($product->getSku(), $stockId);

            return (float) $config->getMaxSaleQty();
        }

        $stockItem = $this->getStockItem($product->getId());

        return (float) $stockItem->getMaxSaleQty();
    }

    /**
     * Get the minimum order quantity for a product.
     *
     * @param ProductModel $product
     * @param ?int         $stockId
     *
     * @return float|null
     */
    public function getMinimumOrderQuantity(ProductModel $product, ?int $stockId): float|null
    {
        if ($this->isMsiActive()) {
            $defaultStockProvider = $this->_objectManager->create(DefaultStockProviderInterface::class);
            $stockId = $stockId ?? $defaultStockProvider->getId();

            $getConfig = $this->_objectManager->create(GetStockItemConfigurationInterface::class);
            $config = $getConfig->execute($product->getSku(), $stockId);

            return (float) $config->getMinSaleQty();
        }

        $stockItem = $this->getStockItem($product->getId());

        return (float) $stockItem->getMinSaleQty();
    }

    /**
     * Get quantity and stock status for environments with MSI dependency
     *
     * @param ProductModel $product
     * @param int|null     $stockId
     *
     * @return mixed[]
     */
    private function getQuantityAndStockStatusWithMSI(ProductModel $product, ?int $stockId = null)
    {
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);
        $qty = $stockItemData[GetStockItemDataInterface::QUANTITY];
        $availability = $this->isProductAvailable($product, $stockId);

        return [$qty, $availability];
    }

    /**
     * Get info about the availability of a product
     * If a product is a grouped product, we consider it is available
     * if any of its associated products is salable
     *
     * @param ProductModel $product
     * @param int|null     $stockId
     *
     * @return boolean
     */
    private function isProductAvailable(ProductModel $product, ?int $stockId = null)
    {
        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach ($associatedProducts as $associatedProduct) {
                if ($this->isProductSalable($associatedProduct, $stockId)) {
                    return true;
                }
            }
            return false;
        }

        return $this->isProductSalable($product, $stockId);
    }

    /**
     * Get info about the salability of any product
     *
     * @param ProductModel $product
     * @param int|null     $stockId
     *
     * @return boolean
     */
    private function isProductSalable(ProductModel $product, ?int $stockId = null)
    {
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);
        return $stockItemData[GetStockItemDataInterface::IS_SALABLE];
    }

    /**
     * Get product availability for environments with MSI dependency
     *
     * @param ProductModel $product
     * @param int|null     $stockId
     *
     * @return string
     */
    private function getProductAvailabilityWithMSI(ProductModel $product, ?int $stockId = null)
    {
        $stockItemData = $this->getStockItemData($product->getSku(), $stockId);

        return $stockItemData[GetStockItemDataInterface::IS_SALABLE]
            ? $this->getInStockLabel()
            : $this->getOutOfStockLabel();
    }

    /**
     * Get the data from a stock item
     *
     * @param string   $sku
     * @param int|null $stockId
     *
     * @return mixed[]
     */
    private function getStockItemData(string $sku, ?int $stockId = null)
    {
        $defaultStockProvider = $this->_objectManager->create(DefaultStockProviderInterface::class);
        $getStockItemData = $this->_objectManager->create(GetStockItemDataInterface::class);
        $stockId = $stockId ?? $defaultStockProvider->getId();

        try {
            $stockItemData = $getStockItemData->execute($sku, $stockId);
        } catch (\Exception $e) {
            $errorMsg = 'Could not receive Stock Item data: ' . $e->getMessage();
            $this->_logger->error(
                $errorMsg,
                [
                'sku' => $sku,
                'stockId' => $stockId,
                'exception' => $e->getMessage(),
                'isMsiActive' => $this->isMsiActive()
                ]
            );
            throw new DoofinderFeedException($errorMsg . ' SKU: ' . $sku . ', stockId: ' . $stockId);
        }

        return [
            GetStockItemDataInterface::QUANTITY => $stockItemData[GetStockItemDataInterface::QUANTITY] ?? 0,
            GetStockItemDataInterface::IS_SALABLE =>
                (bool)($stockItemData[GetStockItemDataInterface::IS_SALABLE] ?? false)
        ];
    }

    /**
     * Get quantity and stock status for environments without MSI dependency
     *
     * @param ProductModel $product
     *
     * @return mixed[]
     */
    private function getQuantityAndStockStatusWithoutMSI(ProductModel $product)
    {
        $qty = $this->getStockItem($product->getId())->getQty();
        $availability = $this->getStockItem($product->getId())->getIsInStock();

        return [$qty, $availability];
    }

    /**
     * Get product availability for environments without MSI dependency
     *
     * @param  ProductModel $product
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
     * @param  integer $productId
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
     * @param  int $storeId
     * @return int|null
     */
    private function getStockIdByStoreWithMSI(int $storeId): ?int
    {
        try {
            $getAssignedStockIdForWebsite = $this->_objectManager->create(GetAssignedStockIdForWebsite::class);
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $stockId = $getAssignedStockIdForWebsite->execute($websiteCode);
            return is_numeric($stockId) ? (int) $stockId : null;
        } catch (\Exception $e) {
            $this->_logger->error(
                'Could not get stockId for store',
                [
                'storeId' => $storeId,
                'exception' => $e->getMessage(),
                ]
            );
            return null;
        }
    }

    /**
     * Function to detect if MSI module is active or not.
     *
     * For the moment is enough checking those two dependencies
     * because we're working only with those.
     *
     * @return bool
     */
    private function isMsiActive(): bool
    {
        return $this->moduleManager->isEnabled('Magento_InventorySalesApi')
            && $this->moduleManager->isEnabled('Magento_InventoryCatalogApi');
    }
}
