<?php

namespace Doofinder\Feed\Controller\Index;

/**
 * Class Index
 *
 * @package Doofinder\Feed\Controller\Index
 */
class Index extends \Doofinder\Feed\Controller\Base
{

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context, $jsonResultFactory);
    }

    /**
     * Execute.
     *
     */
    public function execute()
    {
    }
}
