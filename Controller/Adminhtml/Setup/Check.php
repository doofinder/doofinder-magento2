<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Setup;

use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Webapi\Exception as WebapiException;
use Psr\Log\LoggerInterface;

class Check extends Action implements HttpGetActionInterface
{
    private const INSTALLING_LOOP_STEP = 1;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var Escaper */
    protected $escaper;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Setup constructor.
     *
     * @param Action\Context $context
     * @param StoreConfig $storeConfig
     * @param JsonFactory $resultJsonFactory
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        StoreConfig $storeConfig,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->storeConfig = $storeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Test if the API KEY is set and not empty
     *
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $result = $this->storeConfig->getApiKey() && !empty($this->storeConfig->getApiKey());
            $resultJson->setData($result);
        } catch (Exception $e) {
            $this->storeConfig->setInstallingLoopStatus(self::INSTALLING_LOOP_STEP);
            $this->logger->error('Initial Setup error: ' . $e->getMessage());
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(__('Checking if API Key is set'));
        }

        return $resultJson;
    }
}
