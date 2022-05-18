<?php declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Setup;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Block\Adminhtml\Integration\Tokens;

class AccessToken extends Action implements HttpGetActionInterface
{
    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    public function __construct(
        IntegrationServiceInterface $integrationService,
        Context $context
    ) {
        $this->integrationService = $integrationService;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $integrationId = (int)$this->getRequest()->getParam('id');
        if ($integrationId) {
            try {
                $integration = $this->integrationService->get($integrationId);
                $resultJson->setData([
                    'accessToken' => $integration->getData(Tokens::DATA_TOKEN),
                ]);
            } catch (Exception $e) {
                $resultJson->setData(['_redirect' => $this->getUrl('*/*/')]);
            }
        } else {
            $resultJson->setData(['_redirect' => $this->getUrl('*/*/')]);
        }

        return $resultJson;
    }
}
