<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Service\InstallationService;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Psr\Log\LoggerInterface;

class CreateStore extends Action implements HttpGetActionInterface
{
    /** @var InstallationService */
    private $installationService;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Constructor to initialize dependencies.
     *
     * @param InstallationService $installationService
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        InstallationService $installationService,
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->installationService = $installationService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $installationResults = $this->installationService->generateDoofinderStores();

            $installationFailed = true;

            $message = 'Doofinder stores generated successfully.';
            foreach ($installationResults as $result) {
                if (true !== $result) {
                    $message = __('The installation was not fully successful. Please check the logs for further information.');
                    $installationFailed &= true;
                } else {
                    $installationFailed = false;
                }
            }

            if ($installationFailed) {
                $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
                $resultJson->setData(['success' => false, 'message' => 'Failed to generate Doofinder stores.']);
            } else {
                $resultJson->setData(['success' => true, 'message' => $message]);
            }
        } catch (Exception $e) {
            $this->logger->error('Error during Doofinder store creation: ' . $e->getMessage());
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(['success' => false, 'message' => 'An unexpected error occurred.']);
        }

        return $resultJson;
    }
}
