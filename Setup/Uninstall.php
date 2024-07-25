<?php declare(strict_types=1);

namespace Doofinder\Feed\Setup;

use Exception;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigData;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    private const ITEM_TABLE = 'doofinder_feed_changed_item';

    /**
     * @var ConfigCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ConfigData
     */
    protected $configResource;

    /**
     * @param ConfigCollectionFactory $collectionFactory
     * @param ConfigData $configResource
     */
    public function __construct(
        ConfigCollectionFactory $collectionFactory,
        ConfigData $configResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configResource    = $configResource;
    }

    /**
     * @inheritDoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        //remove table
        if ($setup->tableExists(self::ITEM_TABLE)) {
            $setup->getConnection()->dropTable(self::ITEM_TABLE);
        }
        //remove config settings if any
        $collection = $this->collectionFactory->create();
        $collection->addPathFilter('doofinder_config_config');
        foreach ($collection as $config) {
            $this->deleteConfig($config);
        }
        //remove cron entries
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('path', 'doofinder_config_config/update_on_save/cron_expression');
        $config = $collection->getFirstItem();
        $this->deleteConfig($config);
    }

    /**
     * Deletes the corresponding configuration
     * 
     * @param AbstractModel $config
     * @throws Exception
     */
    protected function deleteConfig(AbstractModel $config)
    {
        $this->configResource->delete($config);
    }
}
