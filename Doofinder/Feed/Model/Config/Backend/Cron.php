<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Errors\DoofinderFeedException;
use Doofinder\Feed\Helper\StoreConfigFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;

class Cron extends ConfigValue
{
    public const CRON_STRING_PATH = 'doofinder_config_config/update_on_save/cron_expression';

    /**
     * @var StoreConfigFactory
     */
    protected $storeConfigFactory;

    /** @var ConfigResource */
    private $configResource;

    /**
     * Cron constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param StoreConfigFactory $storeConfigFactory
     * @param TypeListInterface $cacheTypeList
     * @param ConfigResource $configResource
     * @param array $data
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        StoreConfigFactory $storeConfigFactory,
        TypeListInterface $cacheTypeList,
        ConfigResource $configResource,
        array $data = [],
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null
    ) {
        $this->configResource = $configResource;
        $this->storeConfigFactory = $storeConfigFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Actions to take after saving the periodicity of cron for update on save
     *
     * @return Cron
     * @throws DoofinderFeedException
     */
    public function afterSave(): Cron
    {
        if ($this->storeConfigFactory->create()->isUpdateOnSave()) {
            $cronExpression = $this->getData('groups/update_on_save/fields/cron_expression/value');
            try {
                $this->configResource->load($this, self::CRON_STRING_PATH, 'path');
                $this->setValue($cronExpression)->setPath(self::CRON_STRING_PATH);
                $this->configResource->save($this);
            } catch (\Exception $e) {
                throw new DoofinderFeedException(__('We can\'t save the cron expression.'));
            }
        }

        return parent::afterSave();
    }
}
