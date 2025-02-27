<?php

declare(strict_types=1);


namespace Doofinder\Feed\Controller\Setup;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Config extends Action implements HttpPostActionInterface
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param StoreConfig $storeConfig
     * @param JsonFactory $resultJsonFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig,
        JsonFactory $resultJsonFactory,
        EncryptorInterface $encryptor
    ) {
        $this->storeConfig = $storeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * Store the info related with doofinder platform
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $installerToken = $this->getRequest()->getParam('token');
        $apiToken       = $this->getRequest()->getParam('api_token');
        $apiEndpoint    = $this->getRequest()->getParam('api_endpoint');
        if ($installerToken) {
            $redirect = $this->storeConfig->getDoofinderConnectUrl();
            $tmpToken = $this->encryptor->hash($redirect);
            if ($tmpToken === $installerToken) {
                if ($apiToken) {
                    $region = $this->getRegionFromApiEndpoint($apiEndpoint);
                    $this->storeConfig->setApiKey($region . '-' . $apiToken);
                }
                return $resultJson->setData(['result' => true]);
            } else {
                $msgError = 'Forbidden access. Token for autoinstaller invalid.';
                return $resultJson->setData(['result' => false, 'error' => $msgError]);
            }
        }
        return $resultJson->setData(['result' => true]);
    }

    /**
     * Get cluster region from Doofinder API Endpoint
     *
     * @param string|null $apiEndpoint
     * @return string
     */
    private function getRegionFromApiEndpoint(?string $apiEndpoint): string
    {
        $region = 'eu1';
        if ($apiEndpoint) {
            $apiEndpointParts = explode('-', str_replace('https://', '', $apiEndpoint));
            if (preg_match("/^[a-z]{2}[0-9]$/", $apiEndpointParts[0]) === 1) {
                $region = $apiEndpointParts[0];
            }
        }

        return $region;
    }
}
