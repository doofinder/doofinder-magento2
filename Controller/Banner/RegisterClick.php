<?php

namespace Doofinder\Feed\Controller\Banner;

/**
 * Register banner click.
 */
class RegisterClick extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Doofinder\Feed\Helper\Banner
     */
    private $banner;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Doofinder\Feed\Helper\Banner $banner
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Doofinder\Feed\Helper\Banner $banner
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->banner = $banner;
        parent::__construct($context);
    }

    /**
     * Register banner click.
     *
     * @return string
     */
    public function execute()
    {
        $bannerId = (int) $this->getRequest()->getParam('bannerId', false);

        $this->banner->registerBannerClick($bannerId);

        $result = $this->resultJsonFactory->create();
        return $result->setData(
            [
                'bannerId' => $bannerId
            ]
        );
    }
}
