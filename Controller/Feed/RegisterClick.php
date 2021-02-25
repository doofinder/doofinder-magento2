<?php

namespace Doofinder\Feed\Controller\Feed;

use Doofinder\Feed\Model\Api\Search;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Register result click.
 */
class RegisterClick extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Doofinder\Feed\Model\Api\Search
     */
    private $search;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Doofinder\Feed\Model\Api\Search $search
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Search $search
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->search = $search;
    }

    /**
     * Register banner click.
     *
     * @return string
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('productId', false);
        $query = $this->getRequest()->getParam('query');

        $this->search->registerResultClick($productId, $query);
        $result = $this->resultJsonFactory->create();

        return $result->setData(
            [
                'productId' => $productId
            ]
        );
    }
}
