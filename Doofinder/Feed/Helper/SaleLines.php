<?php

declare(strict_types=1);

namespace Doofinder\Feed\Helper;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * The lines of an order or a cart as Doofinder indexes their products.
 *
 * A configurable product takes the id of its chosen variant, which is indexed, with the quantity and
 * the price of its own line: the variant's line comes at 0. A bundle is its own line, which carries
 * the price. A product of a grouped product is indexed only if it is visible in search, so else it
 * takes the id of the grouped product.
 *
 * The price is the unit price before discounts, in the currency of the order or cart, with taxes
 * unless the store displays its prices without them, as the feed takes it (see the Price helper).
 */
class SaleLines
{
    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * SaleLines constructor.
     *
     * @param TaxConfig $taxConfig
     */
    public function __construct(TaxConfig $taxConfig)
    {
        $this->taxConfig = $taxConfig;
    }

    /**
     * The lines of an order.
     *
     * @param Order $order
     * @return array
     */
    public function fromOrder(Order $order): array
    {
        $withTaxes = $this->withTaxes((int) $order->getStoreId());
        $lines = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $price = $withTaxes ? $item->getPriceInclTax() : $item->getPrice();
            $lines[] = $this->line($item, $item->getChildrenItems(), (float) $item->getQtyOrdered(), (float) $price);
        }

        return $lines;
    }

    /**
     * The lines of a cart.
     *
     * @param Quote $quote
     * @return array
     */
    public function fromQuote(Quote $quote): array
    {
        $withTaxes = $this->withTaxes((int) $quote->getStoreId());
        $lines = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            // A cart line's `price` is in the base currency; its calculation price, which becomes the
            // order line's `price`, is in the cart's.
            $price = $withTaxes ? $item->getPriceInclTax() : $item->getCalculationPrice();
            $lines[] = $this->line($item, $item->getChildren(), (float) $item->getQty(), (float) $price);
        }

        return $lines;
    }

    /**
     * A line of an order or cart, given its child lines and its unit price.
     *
     * @param Order\Item|Quote\Item $item
     * @param array $children
     * @param float $quantity
     * @param float $price
     * @return array
     */
    private function line($item, array $children, float $quantity, float $price): array
    {
        return [
            'id' => (string) $this->indexedId($item, $children),
            'quantity' => $quantity,
            'price' => round($price, 2),
            'title' => $item->getName(),
        ];
    }

    /**
     * The id the product of a line is indexed with.
     *
     * @param Order\Item|Quote\Item $item
     * @param array $children
     * @return int|string
     */
    private function indexedId($item, array $children)
    {
        if ($item->getProductType() === 'configurable' && !empty($children)) {
            return reset($children)->getProductId();
        }

        $grouped = $item->getBuyRequest()->getData('super_product_config');
        if (is_array($grouped) && ($grouped['product_type'] ?? null) === 'grouped') {
            $product = $item->getProduct();
            $visibleInSearch = [Visibility::VISIBILITY_IN_SEARCH, Visibility::VISIBILITY_BOTH];
            if (!$product || !in_array((int) $product->getVisibility(), $visibleInSearch, true)) {
                return $grouped['product_id'];
            }
        }

        return $item->getProductId();
    }

    /**
     * Whether the prices go with taxes.
     *
     * @param int $storeId
     * @return bool
     */
    private function withTaxes(int $storeId): bool
    {
        return (int) $this->taxConfig->getPriceDisplayType($storeId) !== TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX;
    }
}
