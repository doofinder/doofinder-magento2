<?php

declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\Serializer\Base64GzJson;
use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CustomAttributes extends ArraySerialized implements ProcessorInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * CustomAttributes constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ManagerInterface $messageManager
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ManagerInterface $messageManager,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null
    ) {
        $this->messageManager = $messageManager;
        $this->serializer = new Base64GzJson();
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, [], $this->serializer);
    }

    /**
     * Validate value
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $this->messageManager->addNoticeMessage(
            __('To apply the changes in the custom attributes to your feed, it will be necessary to reindex it')
        );

        // For value validations
        $exceptions = $this->getValue();

        $this->setValue($exceptions);

        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    public function processValue($value)
    {
        return empty($value) ? '' : $this->serializer->unserialize($value);
    }
}
