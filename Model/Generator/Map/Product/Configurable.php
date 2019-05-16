<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use \Doofinder\Feed\Model\Generator\Map\Product;

/**
 * Configurable product map
 */
class Configurable extends Product
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Map\Product\AssociateFactory
     */
    private $mapFactory;

    /**
     * @var boolean
     */
    private $grouped;

    /**
     * @var \Doofinder\Feed\Model\Generator\Map[]
     */
    private $associatesMaps = [];

    /**
     * Class constructor
     *
     * @param \Doofinder\Feed\Model\Generator\Map\Product\AssociateFactory $mapFactory
     * @param \Doofinder\Feed\Helper\Product $helper
     * @param \Doofinder\Feed\Model\Generator\Item $item
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Model\Generator\Map\Product\AssociateFactory $mapFactory,
        \Doofinder\Feed\Helper\Product $helper,
        \Doofinder\Feed\Model\Generator\Item $item,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->mapFactory = $mapFactory;
        parent::__construct($helper, $item, $taxConfig, $priceCurrency, $data);
    }

    /**
     * Handle associate items skip before basic mapping
     *
     * @return void
     */
    public function before()
    {
        $this->grouped = !$this->getSplitConfigurableProducts();

        if ($this->grouped) {
            $associates = $this->item->getAssociates();
            /**
            * Set all items as skipped
            * @notice Compatible with PHP5.3+
            */
            array_walk($associates, function ($associate) {
                $associate->skip();
            });
        }
    }

    /**
     * Get associate data if value not exists in parent item
     *
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        // Don't merge prices
        switch ($field) {
            case 'df_regular_price':
            case 'df_sale_price':
            case 'df_minimal_tier_price':
            case 'price':
            case 'special_price':
            case 'tier_price':
            case 'minimal_price':
                return parent::get($field);
        }

        // Only merge associated items values if option is enabled
        if ($this->grouped) {
            switch ($field) {
                case 'df_availability':
                    return $this->getAssociatesAvailability();
            }

            return $this->getGroupedField($field);
        }

        return parent::get($field);
    }

    /**
     * Map availability of associated items
     *
     * @return mixed
     */
    private function getAssociatesAvailability()
    {
        $value = parent::get('df_availability');

        // Return out of stock if configurable product is out of stock
        if ($value == $this->helper->getOutOfStockLabel()) {
            return $value;
        }

        // Return out of stock label if all associated products are out of stock
        $associatesValues = $this->getAssociatesValues('df_availability');
        if (array_unique($associatesValues) == [$this->helper->getOutOfStockLabel()]) {
            return $this->helper->getOutOfStockLabel();
        }

        // Return in stock otherwise
        return $value;
    }

    /**
     * Map field of merged values of associated items and configurable product
     *
     * @param string $field
     * @return mixed
     */
    private function getGroupedField($field)
    {
        // Get configurable product value
        $value = parent::get($field);

        // Get values of associated items
        $associatesValues = $this->getAssociatesValues($field);

        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_merge($value, $associatesValues);

        // Remove duplicates
        $value = array_unique($value);

        // Filter out empty values (0 is not an empty value)
        $value = array_filter($value, function ($item) {
            return $item || $item === 0;
        });

        // Reset keys
        $value = array_values($value);

        if (!$value) {
            return $value;
        }

        if (count($value) > 1) {
            return $value;
        }

        // Remove array if value is single
        return $value[0];
    }

    /**
     * Get value of associated item
     *
     * @param string $field
     * @return mixed
     */
    private function getAssociatesValues($field)
    {
        $associatesValues = [];

        foreach ($this->item->getAssociates() as $associate) {
            $associatesValues[] = $this->getAssociateMap($associate)->get($field);
        }

        /**
         * Flatten array recursively
         */
        $flattened = [];
        array_walk_recursive($associatesValues, function ($item) use (&$flattened) {
            $flattened[] = $item;
        });
        $associatesValues = $flattened;

        /**
         * Filter out null values
         * @notice Compatible with PHP5.3+
         */
        return array_filter($associatesValues, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Get value of associated item map
     *
     * @param \Doofinder\Feed\Model\Generator\Item $associate
     * @return \Doofinder\Feed\Model\Generator\Map
     */
    private function getAssociateMap(\Doofinder\Feed\Model\Generator\Item $associate)
    {
        $hash = spl_object_hash($associate);

        if (!isset($this->associatesMaps[$hash])) {
            $this->associatesMaps[$hash] = $this->mapFactory->create([
                'item' => $associate,
                'data' => $this->getData(),
            ]);
        }

        return $this->associatesMaps[$hash];
    }
}
