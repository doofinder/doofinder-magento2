<?php
declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\Tax\Model\Config as TaxConfig;

class Price extends AbstractHelper
{
    private $taxConfig;

    public function __construct(
        Context $context,
        TaxConfig $taxConfig
    ) {
        $this->taxConfig = $taxConfig;
        parent::__construct($context);
    }

    /**
     * Gets product price by product's id
     */
    public function getProductPrice($product, $type = 'final_price')
    {
        $price_type = $this->getPriceType($type);
        $flat_price = $this->getProductFlatPrice($product, $price_type);
        $price = $this->getPriceApplyingCorrespondingTaxes($product, $flat_price);
        return $price;
    }

    /**
     * Returns the price type transformed to one of the types we use
     */
    private function getPriceType($type)
    {
        switch ($type) {
            case 'special_price':
            case 'tier_price':
            case 'regular_price':
                return $type;
            default:
                return 'final_price';
        }
    }

    /**
     * Gets the flat price of the product based on it's type
     */
    private function getProductFlatPrice($product, $type)
    {
        switch($product->getTypeId()) {
            case GroupedType::TYPE_CODE:
                return $this->getGroupedProductPrice($product, $type);

            case ConfigurableType::TYPE_CODE:
                return $this->getConfigurableProductPrice($product, $type);

            case ProductType::TYPE_BUNDLE:
                return $this->getBundleProductPrice($product, $type);

            case ProductType::TYPE_SIMPLE:
            case ProductType::TYPE_VIRTUAL:
            case DownloadableType::TYPE_DOWNLOADABLE:
                return $product->getPriceInfo()->getPrice($type);

            default:
                return 0;
        }
    }

    /**
     * Gets the price applying the taxes in case it's necessary
     */
    private function getPriceApplyingCorrespondingTaxes($product, $price)
    {
        if (!$price)
            return 0;

        $amount = $price->getAmount();
        $this->getTaxEnabled() ?
            $calculatedPrice = $this->getPriceWithTaxes($product, $amount):
            $calculatedPrice = $amount->getBaseAmount();

        return (float)$calculatedPrice;
    }

    /**
     * Function that returns the price with the corresponding tax value.
     * The first case contemplates the scenario of the tax already applied to the price
     * The second scenario needs this adjustment to be applied.
     */
    private function getPriceWithTaxes($product, $amount)
    {
        $this->taxConfig->priceIncludesTax() ?
            $value = $amount->getValue():
            $value = $product
                ->getPriceInfo()
                ->getAdjustment('tax')
                ->applyAdjustment($amount->getBaseAmount(), $product);

        return $value;
    }

    /**
     * Returns whether the taxes are enabled in the backoffice or not
     */
    private function getTaxEnabled()
    {
        return $this->taxConfig->getPriceDisplayType() != TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * Applies the pricing strategy for bundle-type products and returns the corresponding value
     */
    private function getBundleProductPrice($product, $type)
    {
        if ($type === 'special_price') {
            $type = 'final_price';
        }
        return $product->getPriceInfo()->getPrice($type);
    }

    /**
     * Applies the pricing strategy for grouped-type products and returns the corresponding value
     */
    private function getGroupedProductPrice($product, $type)
    {
        if($type !== 'regular_price') {
            return $product->getPriceInfo()->getPrice($type);
        }

        $usedProds = $product->getTypeInstance()->getAssociatedProducts($product);
        return $this->getMinimumComplexProductPrice($product, $usedProds, $type);
    }

    /**
     * Applies the pricing strategy for configurable-type products and returns the corresponding value
     */
    private function getConfigurableProductPrice($product, $type)
    {
        $usedProds = $product->getTypeInstance()->getUsedProducts($product);
        return $this->getMinimumComplexProductPrice($product, $usedProds, $type);
    }

    private function getMinimumComplexProductPrice($product, $usedProds, $type) {
        $prices = [];
        foreach ($usedProds as $child) {
            if ($child->getId() != $product->getId()) {
                $price = $child->getPriceInfo()->getPrice($type);
                $prices['prices'][] =  $price;
                $prices['values'][] =  $price->getAmount()->getValue();
            }
        }

        if (empty($prices))
            return null;

        $index = array_search(min($prices['values']), $prices['values']);
        return ($index < 0) ? null : $prices['prices'][$index];
    }
}
