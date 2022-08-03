<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Psr\Log\LoggerInterface;

class SaveSector extends Action implements HttpPostActionInterface
{
    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var WriterInterface */
    private $configWriter;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        WriterInterface $configWriter,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->configWriter = $configWriter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
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
            $sectorValue = array_keys($this->getRequest()->getPostValue())[0];
            $this->configWriter->save(StoreConfig::SECTOR_VALUE_CONFIG, $sectorValue);
            $resultJson->setData(true);
        } catch (Exception $e) {
            $resultJson->setData([
                'result' => false,
                'error' => $this->escaper->escapeHtml($e->getMessage()),
            ]);
            $this->logger->error('There was a problem saving the sector: ' . $e->getMessage());
        }

        return $resultJson;
    }
}