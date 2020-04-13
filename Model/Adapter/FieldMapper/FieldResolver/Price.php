<?php

namespace Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Price
 * The class responsible for retrieving price field name based on Customer Group
 */
class Price
{
    const ATTR_NAME = 'mage_price_';

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Price constructor.
     * @param CustomerSession $customerSession
     */
    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @param array $context
     * @return string
     */
    public function getFiledName(array $context = [])
    {
        $customerGroupId = empty($context['customer_group_id'])
            ? $this->customerSession->getCustomerGroupId()
            : $context['customer_group_id'];
        return self::ATTR_NAME  . $customerGroupId;
    }
}
