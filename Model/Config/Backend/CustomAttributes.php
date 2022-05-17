<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CustomAttributes extends ArraySerialized
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * CustomAttributes constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection);
    }
    
    /**
     * Validate value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $this->messageManager->addNoticeMessage(__('It will be necessary to reindex your feed to so that the changes in the custom attributes will be applied on your feed'));
        
        // For value validations
        $exceptions = $this->getValue();

        $this->setValue($exceptions);

        return parent::beforeSave();
    }
}
