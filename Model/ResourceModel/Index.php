<?php

namespace Doofinder\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Dimension;
use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

// phpcs:disable

/**
 * Class Index
 * The class used for backward compatibility
 * @SuppressWarnings(PHPMD.LongVariable)
 * @see \Magento\AdvancedSearch\Model\ResourceModel\Index
 * @TODO: consider refactoring
 */
class Index extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var DimensionCollectionFactory
     */
    private $dimensionCollectionFactory;

    /**
     * @var integer|null
     */
    private $websiteId;

    /**
     * Index constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param TableResolver $tableResolver
     * @param DimensionCollectionFactory $dimensionCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        TableResolver $tableResolver,
        DimensionCollectionFactory $dimensionCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->tableResolver = $tableResolver;
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
    }

    /**
     * Implementation of abstract construct
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Return array of price data per customer and website by products
     *
     * @param array $productIds
     * @return array
     */
    private function _getCatalogProductPriceData(array $productIds = [])
    {
        $connection = $this->getConnection();
        $catalogProductIndexPriceSelect = [];

        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            if (!isset($dimensions[WebsiteDimensionProvider::DIMENSION_NAME]) ||
                $this->websiteId === null ||
                $dimensions[WebsiteDimensionProvider::DIMENSION_NAME]->getValue() === $this->websiteId) {
                $select = $connection->select()->from(
                    $this->tableResolver->resolve('catalog_product_index_price', $dimensions),
                    ['entity_id', 'customer_group_id', 'website_id', 'min_price']
                );
                if ($productIds) {
                    $select->where('entity_id IN (?)', $productIds);
                }
                $catalogProductIndexPriceSelect[] = $select;
            }
        }

        $catalogProductIndexPriceUnionSelect = $connection->select()->union($catalogProductIndexPriceSelect);

        $result = [];
        foreach ($connection->fetchAll($catalogProductIndexPriceUnionSelect) as $row) {
            $result[$row['website_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }

        return $result;
    }

    /**
     * Retrieve price data for product
     *
     * @param array|null $productIds
     * @param integer|null $storeId
     * @return array
     */
    public function getPriceIndexData(array $productIds, $storeId = null)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        $this->websiteId = $websiteId;
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);
        $this->websiteId = null;

        if (!isset($priceProductsIndexData[$websiteId])) {
            return [];
        }

        return $priceProductsIndexData[$websiteId];
    }

    /**
     * Prepare system index data for products.
     *
     * @param integer|null $storeId
     * @param array $productIds
     * @return array
     */
    public function getCategoryProductIndexData($storeId = null, array $productIds = [])
    {
        $connection = $this->getConnection();

        $catalogCategoryProductDimension = new Dimension(\Magento\Store\Model\Store::ENTITY, $storeId);

        $catalogCategoryProductTableName = $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [
                $catalogCategoryProductDimension
            ]
        );

        $select = $connection->select()->from(
            [$catalogCategoryProductTableName],
            ['category_id', 'product_id', 'position', 'store_id']
        )->where(
            'store_id = ?',
            $storeId
        );

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][$row['category_id']] = $row['position'];
        }

        return $result;
    }
}
