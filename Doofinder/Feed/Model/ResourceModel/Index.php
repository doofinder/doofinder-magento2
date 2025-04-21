<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Store\Model\StoreManagerInterface;

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
        string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->tableResolver = $tableResolver;
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
    }

    /**
     * Implementation of abstract construct.
     *
     * DO NOT REMOVE since it's required
     */
    protected function _construct()
    {
        return true;
    }
}
