<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\Indice as IndiceHelper;
use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Block\Adminhtml\Integration\Tokens;
use Psr\Log\LoggerInterface;

class CreateIndices extends Action implements HttpGetActionInterface
{
    private const INSTALLING_LOOP_STEP = 3;

    /** @var StoreConfig */
    private $storeConfig;

    /** @var IndiceHelper */
    private $indiceHelper;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var Escaper */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    public function __construct(
        StoreConfig $storeConfig,
        IndiceHelper $indiceHelper,
        JsonFactory $resultJsonFactory,
        Escaper $escaper,
        UrlInterface $urlInterface,
        LoggerInterface $logger,
        IntegrationServiceInterface $integrationService,
        Context $context
    ) {
        $this->storeConfig = $storeConfig;
        $this->indiceHelper = $indiceHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper = $escaper;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
        $this->integrationService = $integrationService;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     *
     * @throws WebapiException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            foreach ($this->storeConfig->getAllStores() as $store) {
                $hashId = $this->storeConfig->getHashId((int)$store->getId());
                $integrationToken = $this->integrationService
                    ->get($this->storeConfig->getIntegrationId())
                    ->getData(Tokens::DATA_TOKEN);
                if ($hashId) {
                    $indice = [
                        'name'   => IndiceHelper::MAGENTO_INDICE_NAME,
                        'preset' => 'product',
                        'datasources' => [
                            [
                                'type' => 'magento2',
                                'options' => [
                                    'url' => $this->urlInterface->getBaseUrl() . 'rest/' . $store->getCode() . '/V1/',
                                    'token' => $integrationToken,
                                ],
                            ],
                        ],
                    ];
                    $this->indiceHelper->createIndice($indice, $hashId);
                }
            }
            $resultJson->setData(true);
        } catch (Exception $e) {
            $this->storeConfig->setInstallingLoopStatus(self::INSTALLING_LOOP_STEP);
            $this->logger->error('Initial Setup error: ' . $e->getMessage());
            $resultJson->setHttpResponseCode(WebapiException::HTTP_INTERNAL_ERROR);
            $resultJson->setData(__('Create Indices'));
        }

        return $resultJson;
    }
}
