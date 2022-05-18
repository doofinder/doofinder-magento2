<?php
declare(strict_types=1);


namespace Doofinder\Feed\Controller\Setup;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Config extends Action implements CsrfAwareActionInterface
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param StoreConfig $storeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        RawFactory $resultRawFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Bypass CSRF validation
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Listen the callback defined in Doofinder side
     *
     * @return RawResult
     * @throws NoSuchEntityException
     */
    public function execute(): RawResult
    {
        $result         = $this->resultRawFactory->create();
        $installerToken = $this->getRequest()->getParam('token');
        $apiToken       = $this->getRequest()->getParam('api_token');
        $apiEndpoint    = $this->getRequest()->getParam('api_endpoint');
        if ($installerToken) {
            $redirect = $this->getRedirectUrl();
            $tmpToken = $this->encryptor->hash($redirect);
            if ($tmpToken === $installerToken) {
                if ($apiToken) {
                    $region = $this->getRegionFromApiEndpoint($apiEndpoint);
                    $this->storeConfig->setApiKey($region . '-' . $apiToken);
                }
                $result->setContents('OK');
            } else {
                $msgError = 'Forbidden access. Token for autoinstaller invalid.';
                $result->setContents($msgError);
            }
        }
        $result->setContents('OK');

        return $result;
    }

    /**
     * Get redirect URL
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getRedirectUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl()
            . 'doofinderfeed/setup/config';
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
            $apiEndpointParts = explode('-', $apiEndpoint);
            $region = $apiEndpointParts[0];
        }

        return $region;
    }
}
