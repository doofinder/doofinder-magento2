<?php
declare(strict_types=1);


namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Errors\InvalidArgumentException;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\IntegrationException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Controller\Adminhtml\Integration;

class SaveConfig extends Action implements HttpPostActionInterface
{
    /** @var WriterInterface */
    private $configWriter;

    /** @var IntegrationServiceInterface */
    private $integrationService;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var Escaper */
    protected $escaper;

    public function __construct(
        WriterInterface $configWriter,
        IntegrationServiceInterface $integrationService,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        Context $context
    ) {
        $this->configWriter = $configWriter;
        $this->integrationService = $integrationService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $integrationId = (int)$this->getRequest()->getParam(Integration::PARAM_INTEGRATION_ID);
            if (!$integrationId) {
                throw new InvalidArgumentException((string)__('Integration ID param is missing'));
            }
            $this->integrationService->get($integrationId);
            $this->configWriter->save(StoreConfig::INTEGRATION_ID_CONFIG, $integrationId);
            $resultJson->setData(['result' => true]);
        } catch (InvalidArgumentException | IntegrationException $e) {
            $resultJson->setData([
                'result' => false,
                'error' => $this->escaper->escapeHtml($e->getMessage()),
            ]);
        }

        return $resultJson;
    }
}
