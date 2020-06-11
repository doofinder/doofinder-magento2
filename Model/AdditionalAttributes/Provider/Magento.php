<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;

/**
 * Class Magento
 * The class responsible for providing Magento attributes code
 */
class Magento implements AttributesProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var array
     */
    private $magentoAttributes;

    /**
     * Magento constructor.
     * @param CollectionFactory $collectionFactory
     * @param Config $eavConfig
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Config $eavConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->magentoAttributes) {
            $eavType = $this->eavConfig->getEntityType(Product::ENTITY);
            $attributes = $this->collectionFactory->create()->setEntityTypeFilter($eavType);
            foreach ($attributes as $attribute) {
                $this->magentoAttributes[] = $attribute->getAttributeCode();
            }

            $this->magentoAttributes = array_unique($this->magentoAttributes);
        }

        return $this->magentoAttributes;
    }
}
