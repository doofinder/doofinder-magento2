<?php

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Service\InstallationService;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Psr\Log\LoggerInterface;

class CreateStore extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var InstallationService
     */
    protected $installationService;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CreateStore constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Escaper $escaper
     * @param InstallationService $installationService
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Escaper $escaper,
        InstallationService $installationService,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->installationService = $installationService;
        $this->logger = $logger;
    }

    /**
     * Execute sync action
     */
    public function execute()
    {

        $resultJson = $this->jsonFactory->create();

        try {
            $groupId = (int)$this->getRequest()->getParam('group');
            $group = $this->storeManager->getGroup($groupId);
            $result = $this->installationService->generateDoofinderStore($group);

            return $resultJson->setData([
                'success' => true,
                'data' => $result,
                'message' => __('Store sync initiated for group id: %1', $groupId)
            ]);
        } catch (Exception $e) {
            $message = 'Error creating Doofinder store for store group "' .
                $group->getName() . '". ' . $e->getMessage();
            $this->logger->error($message);
            $resultJson->setData([
                'success' => false,
                'message' => $message,
            ])->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
        }

        return $resultJson;
    }

    /**
     * Check ACL permissions
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Doofinder_Doofinder::config');
    }
}
