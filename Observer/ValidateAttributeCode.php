<?php

namespace Doofinder\Feed\Observer;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ValidateAttributeCode
 * The class responsible for checking, if attribute code is not used in Doofinder Index Settings
 */
class ValidateAttributeCode implements ObserverInterface
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * DoofinderCode constructor.
     * @param StoreConfig $storeConfig
     */
    public function __construct(StoreConfig $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Attribute $model */
        $model = $observer->getEvent()->getDataObject();
        $attributeCode = $model->getAttributeCode();

        if (!$this->storeConfig->isInternalSearchEnabled()) {
            return;
        }

        $attributes = $this->storeConfig->getDoofinderFields();
        $attributeKeys = array_keys($attributes);
        if (in_array($attributeCode, $attributeKeys)) {
            throw new LocalizedException(__(
                'Attribute code %1 is already used in Doofinder Index Settings. '
                . 'Change attribute code here or in Doofinder',
                $attributeCode
            ));
        }
    }
}
