<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Errors\DoofinderFeedException;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Cron extends ConfigValue
{
    const CRON_STRING_PATH = 'crontab/default/jobs/doofinder_update_on_save/schedule/cron_expr';

    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Cron
     * @throws DoofinderFeedException
     */
    public function afterSave(): Cron
    {
        $updateOnSave = $this->getData('groups/update_on_save/fields/enabled/value');
        if ($updateOnSave) {
            $cronExpression = $this->getData('groups/update_on_save/fields/cron_expression/value');
            try {
                $this->configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExpression
                )->setPath(
                    self::CRON_STRING_PATH
                )->save();
            } catch (\Exception $e) {
                throw new DoofinderFeedException(__('We can\'t save the cron expression.'));
            }
        }

        return parent::afterSave();
    }
}
