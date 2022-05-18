<?php

declare(strict_types=1);

namespace Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFieldName(array $context = []): string
    {
        $customerGroupId = empty($context['customer_group_id'])
            ? $this->customerSession->getCustomerGroupId()
            : $context['customer_group_id'];

        return self::ATTR_NAME  . $customerGroupId;
    }
}
