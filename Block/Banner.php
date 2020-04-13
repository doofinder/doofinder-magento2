<?php

namespace Doofinder\Feed\Block;

/**
 * Class Banner
 * The class responsible for rendering Doofinder Banner
 */
class Banner extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Doofinder\Feed\Helper\Banner
     */
    private $banner;

    /**
     * @param \Doofinder\Feed\Helper\Banner $banner
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Doofinder\Feed\Helper\Banner $banner,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->banner = $banner;
        parent::__construct($context);
    }

    /**
     * Get banner data.
     *
     * @return array|null
     */
    public function getBanner()
    {
        return $this->banner->getBanner();
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
