<?php

namespace Doofinder\Feed\Block;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SearchResultItem
 */
class SearchResultItem extends Template
{
    /**
     * CSS class for the anchor element.
     */
    const ANCHOR_ELEMENT_CLASS = 'doofinder-result-item-anchor';

    /**
     * @var string
     */
    protected $_template = 'Doofinder_Feed::search_result_item.phtml';

    /**
     * @var \Magento\Catalog\Model\Product|null
     */
    private $product;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * SearchResultItem constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig
    ) {
        parent::__construct($context);

        $this->storeConfig = $storeConfig;
    }

    /**
     * Check if search is set to Doofinder.
     *
     * @return boolean
     */
    public function isDoofinderEngineEnabled()
    {
        return $this->storeConfig->isInternalSearchEnabled();
    }

    /**
     * @return string
     */
    public function getElementClass()
    {
        return self::ANCHOR_ELEMENT_CLASS;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->product;
    }
}
