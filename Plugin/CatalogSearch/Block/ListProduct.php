<?php

namespace Doofinder\Feed\Plugin\CatalogSearch\Block;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Catalog\Model\Product;

/**
 * Class ListProduct
 */
class ListProduct
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * ListProduct constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        StoreConfig $storeConfig
    ) {
        $this->storeConfig = $storeConfig;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param string $result
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function afterGetProductPrice(\Magento\Catalog\Block\Product\ListProduct $subject, $result, Product $product)
    {
        if ($this->storeConfig->isInternalSearchEnabled()) {
            /** @var \Doofinder\Feed\Block\SearchResultItem $block */
            $block = $subject->getLayout()
                ->createBlock(\Doofinder\Feed\Block\SearchResultItem::class);

            $block->setProduct($product);

            $result .= $block->toHtml();
        }

        return $result;
    }
}
