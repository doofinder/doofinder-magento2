<?php declare(strict_types=1);

namespace Doofinder\Feed\Setup;

use Exception;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigData;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
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
        if ($setup->tableExists('doofinder_feed_changed_product')) {
            $setup->getConnection()->dropTable('doofinder_feed_changed_product');
        }
        //remove config settings if any
        $collection = $this->collectionFactory->create();
        $collection->addPathFilter('doofinder_config_config');
        foreach ($collection as $config) {
            $this->deleteConfig($config);
        }
        //remove cron entries
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('path', 'crontab/default/jobs/doofinder_update_on_save/schedule/cron_expr');
        $config = $collection->getFirstItem();
        $this->deleteConfig($config);
    }

    /**
     * @param AbstractModel $config
     * @throws Exception
     */
    protected function deleteConfig(AbstractModel $config)
    {
        $this->configResource->delete($config);
    }
}
