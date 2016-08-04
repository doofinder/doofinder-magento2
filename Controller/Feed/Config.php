<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Class Config
 *
 * @package Doofinder\Feed\Controller\Feed
 */
class Config extends \Doofinder\Feed\Controller\Base
{

    /**
     * Config constructor.
     *
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