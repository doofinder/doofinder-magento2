<?php

namespace Doofinder\Feed\Search\Dynamic\DataProvider;

use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Store\Model\StoreManager;
use \Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;

/**
 * Class SelectProvider
 * The class responsible for providing select object for DataProvider
 */
class SelectProvider
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var IndexScopeResolverInterface
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * SelectProvider constructor.
     * @param ResourceConnection $resource
     * @param Session $customerSession
     * @param StoreManager $storeManager
     * @param IndexScopeResolverInterface $priceTableResolver
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        ResourceConnection $resource,
        Session $customerSession,
        StoreManager $storeManager,
        IndexScopeResolverInterface $priceTableResolver,
        DimensionFactory $dimensionFactory
    ) {
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * @param EntityStorage $entityStorage
     * @return Select
     */
    public function get(EntityStorage $entityStorage)
    {
        $select = $this->resource->getConnection()->select();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $tableName = $this->priceTableResolver->resolve(
            'catalog_product_index_price',
            [
                $this->dimensionFactory->create(
                    WebsiteDimensionProvider::DIMENSION_NAME,
                    (string)$websiteId
                ),
                $this->dimensionFactory->create(
                    CustomerGroupDimensionProvider::DIMENSION_NAME,
                    (string)$customerGroupId
                ),
            ]
        );

        $productIds = $entityStorage->getSource();
        $select->from(['main_table' => $tableName], [])
            ->where('main_table.entity_id IN (?)', array_values($productIds));

        $select->where('customer_group_id = ?', $customerGroupId);
        $select->where('main_table.website_id = ?', $websiteId);
        return $select;
    }
}
