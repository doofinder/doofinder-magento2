<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Service\InstallationService;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Psr\Log\LoggerInterface;

class Setup extends Action implements HttpGetActionInterface
{
    /** @var InstallationService */
    private $installationService;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var Escaper */
    protected $escaper;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Constructor to initialize dependencies.
     *
     * @param InstallationService $installationService
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        InstallationService $installationService,
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        Escaper $escaper,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->installationService = $installationService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
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

            $failedInstallations = 0;
            $message = 'Doofinder stores generated successfully.';
            foreach ($installationResults as $result) {
                if (true !== $result) {
                    $message = __('Doofinder was successfully installed. However, ' .
                        'not all store views were successfully installed. Please check ' .
                        'the logs for further information.');
                    $failedInstallations++;
                }
            }

            if ($failedInstallations == count($installationResults)) {
                $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
                $resultJson->setData(['success' => false, 'message' => 'Failed to generate Doofinder stores.']);
            } else {
                $resultJson->setData(['success' => true, 'message' => $this->escaper->escapeHtml($message)]);
            }
        } catch (Exception $e) {
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(['success' => false, 'message' => $this->escaper->escapeHtml($e->getMessage())]);
        }

        return $resultJson;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Doofinder_Feed::config');
    }
}
