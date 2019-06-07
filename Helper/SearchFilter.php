<?php

namespace Doofinder\Feed\Helper;

/**
 * Search filters helper
 */
class SearchFilter extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    private $attrCollFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var array|null
     */
    private $filters;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrCollFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrCollFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->attrCollFactory = $attrCollFactory;
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * Get filters array
     *
     * @return array
     */
    public function getFilters()
    {
        if ($this->filters === null) {
            $params = $this->_getRequest()->getParams();
            $this->filters = [];
            foreach ($params as $filter => $value) {
                if ($filter === 'q' || $value === null) {
                    continue;
                }

                $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $filter);
                if ($attribute && $attribute->getAttributeId()) {
                    $filterValue = $this->getFilterValue($attribute, $value);
                    if ($filterValue !== null) {
                        $this->filters[$filter] = $filterValue;
                    }
                }
            }
        }

        return $this->filters;
    }

    /**
     * Get filter value
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param mixed $value
     * @return array|null
     */
    private function getFilterValue(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, $value)
    {
        $inputType = $attribute->getFrontendInput();
        switch ($inputType) {
            case 'price':
                return $this->getPriceRange($value);

            case 'boolean':
                return [(boolean)$value ? 'Yes' : 'No'];

            case 'text':
            case 'textarea':
                return [$value];

            case 'select':
            case 'multiselect':
            default:
                // get value from attribute
                $collection = $this->getAttributeOptionById($attribute->getAttributeId(), $value);
                foreach ($collection as $item) {
                    return [$item->getValue()];
                }
                return null;
        }
    }

    /**
     * Get price range array from-to
     *
     * @param string $value
     * @return array
     */
    private function getPriceRange($value)
    {
        $priceRange = explode('-', $value);
        $result = [];
        if (isset($priceRange[0])) {
            $result['from'] = $priceRange[0];
        }
        if (isset($priceRange[1])) {
            $result['to'] = $priceRange[1];
        }

        return $result;
    }

    /**
     * Get particular option's name and value of the attribute
     *
     * @param integer $attributeId
     * @param mixed $optionId
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    public function getAttributeOptionById($attributeId, $optionId)
    {
        return $this->attrCollFactory
            ->create()
            ->setAttributeFilter($attributeId)
            ->setIdFilter($optionId)
            ->setStoreFilter();
    }
}
