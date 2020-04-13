<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver\Price as PriceFieldNameResolver;

/**
 * Class Price
 * The class responsible for providing Price attributes code
 */
class Price implements AttributesProviderInterface
{
    /**
     * @var CustomerGroupCollectionFactory
     */
    private $groupColFactory;

    /**
     * @var array
     */
    private $attributes;

    /**
     * Price constructor.
     * @param CustomerGroupCollectionFactory $groupColFactory
     */
    public function __construct(CustomerGroupCollectionFactory $groupColFactory)
    {
        $this->groupColFactory = $groupColFactory;
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->attributes) {
            $customerGroups = $this->groupColFactory->create()->toOptionArray();
            foreach ($customerGroups as $customerGroup) {
                $this->attributes[] = PriceFieldNameResolver::ATTR_NAME . $customerGroup['value'];
            }
        }
        return $this->attributes;
    }
}
