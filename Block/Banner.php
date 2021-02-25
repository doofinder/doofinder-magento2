<?php

namespace Doofinder\Feed\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Doofinder\Feed\Helper\Banner as Helper;

/**
 * Class Banner
 * The class responsible for rendering Doofinder Banner
 */
class Banner extends Template
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * Banner constructor.
     * @param Context $context
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Get banner data.
     *
     * @return array|null
     */
    public function getBanner()
    {
        return $this->helper->getBanner();
    }

    /**
     * Get ajax url for registering banner click.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('doofinder/banner/registerclick');
    }
}
