<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;

/**
 * ScopeGroup value backend. Saves and loads an SCOPE_GROUP value for SCOPE_STORE.
 */
class ScopeGroupValue extends Value
{

    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var StoreConfig
     */
    protected $_storeConfig;

    /**
     * ScopeGroupValue constructor.
     *
     * @param StoreConfig $storeConfig
     * @param StoreManager $storeManager
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        StoreConfig $storeConfig,
        StoreManager $storeManager,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save configuration.
     *
     * @return ScopeGroupValue
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $storeGroupId = $this->_storeManager->getStore($this->getScopeId())->getStoreGroupId();
        $this->setScope(ScopeInterface::SCOPE_GROUP);
        $this->setScopeId((int)$storeGroupId);
        return parent::beforeSave();
    }

    /**
     * Load  configuration.
     *
     * @return mixed
     */
    public function getValue()
    {
        $storeGroupId = (int)$this->_storeManager->getStore($this->getStore())->getStoreGroupId();
        return $this->_storeConfig->getValueFromConfig($this->getPath(), ScopeInterface::SCOPE_GROUP, $storeGroupId);
    }
}
