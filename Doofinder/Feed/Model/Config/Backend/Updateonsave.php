<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Errors\DoofinderFeedException;
use Doofinder\Feed\Helper\StoreConfigFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Updateonsave extends ConfigValue
{
    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    protected $storeConfigFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        StoreConfigFactory $storeConfigFactory,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        $this->storeConfigFactory = $storeConfigFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Updateonsave
     * @throws DoofinderFeedException
     */
    public function afterSave(): Updateonsave
    {
        if (!$this->storeConfigFactory->create()->isUpdateOnSave()) {
            try {
                $this->configValueFactory->create()->load(
                    Cron::CRON_STRING_PATH,
                    'path'
                )->delete();
            } catch (\Exception $e) {
                throw new DoofinderFeedException(__('We can\'t save value.'));
            }
        }

        return parent::afterSave();
    }
}
