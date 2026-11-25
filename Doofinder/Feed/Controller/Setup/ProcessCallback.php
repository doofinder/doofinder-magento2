<?php declare(strict_types=1);

namespace Doofinder\Feed\Controller\Setup;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Helper\Indexation;
use Doofinder\Feed\Model\ProcessCallbackToken;
use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Psr\Log\LoggerInterface;

/**
 * Receives the result of the first indexation of a search engine from Doofinder.
 *
 * Doofinder calls the callback URL the module gave it when it created the search engine. The
 * request is only processed when it carries the token of that URL, so nobody else can change the
 * indexation status.
 */
class ProcessCallback extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    private const HTTP_UNAUTHORIZED = 401;

    /** @var StoreConfig */
    private $storeConfig;

    /**
     * Serializer interface instance.
     *
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var ProcessCallbackToken
     */
    private $processCallbackToken;

    /**
     * ProcessCallback constructor.
     *
     * @param StoreConfig $storeConfig
     * @param JsonSerializer $serializer
     * @param LoggerInterface $logger
     * @param TypeListInterface $cacheTypeList
     * @param ProcessCallbackToken $processCallbackToken
     * @param Context $context
     */
    public function __construct(
        StoreConfig $storeConfig,
        JsonSerializer $serializer,
        LoggerInterface $logger,
        TypeListInterface $cacheTypeList,
        ProcessCallbackToken $processCallbackToken,
        Context $context
    ) {
        $this->storeConfig = $storeConfig;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->cacheTypeList = $cacheTypeList;
        $this->processCallbackToken = $processCallbackToken;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $storeId = $this->getStoreIdFromRequest();
        if ($storeId === null || !$this->isAuthenticated($storeId)) {
            // Nothing from the request is logged or processed before it is authenticated
            $this->logger->warning('[ProcessCallback] Rejected a callback without a valid token.');
            $result->setHttpResponseCode(self::HTTP_UNAUTHORIZED);
            $result->setData('KO: Invalid token');
            return $result;
        }

        try {
            $content = (string)$this->getRequest()->getContent();
            $this->logger->debug('[ProcessCallback] Body:');
            $this->logger->debug($content);
            $message = $this->getMessage($content);
            $store = $this->storeConfig->getStoreById($storeId);
            $response = $this->sanitizeResponse($message);
            $this->logger->debug('[ProcessCallback] Status: ' . $response['status'] .
                ' - Message: ' . $response['result']);
            $this->storeConfig->setIndexationStatus($response, (int)$store->getId());
            $this->cleanCache();
            $result->setData('OK');
        } catch (Exception $e) {
            $this->logger->error('[ProcessCallback] Error: ' . $e->getMessage());
            $result->setData('KO: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Check that the request carries the callback token of the store view
     *
     * @param int $storeId
     * @return bool
     */
    private function isAuthenticated(int $storeId): bool
    {
        $token = $this->getRequest()->getParam(ProcessCallbackToken::PARAM_NAME);

        return is_string($token) && $this->processCallbackToken->isValid($storeId, $token);
    }

    /**
     * Get the store view id of the callback URL
     *
     * @return int|null Null when the URL has no valid store view id.
     */
    private function getStoreIdFromRequest(): ?int
    {
        $storeId = $this->getRequest()->getParam('storeId');

        return is_string($storeId) && ctype_digit($storeId) ? (int)$storeId : null;
    }

    /**
     * Get the message of the process callback body
     *
     * @param string $content
     * @return string
     * @throws InvalidArgumentException When the body is not a JSON object with a string message.
     */
    private function getMessage(string $content): string
    {
        $body = $this->serializer->unserialize($content);
        if (!is_array($body) || !isset($body['message']) || !is_string($body['message'])) {
            throw new InvalidArgumentException('The callback body has no message.');
        }

        return $body['message'];
    }

    /**
     * Sanitize process callback message
     *
     * @param string $message
     *
     * @return mixed[]
     */
    private function sanitizeResponse(string $message): array
    {
        $status_boolean = $this->validateProcessMessage($message);
        $date = new \DateTime();
        return [
            'status' => $status_boolean ? Indexation::DOOFINDER_INDEX_PROCESS_STATUS_SUCCESS :
                Indexation::DOOFINDER_INDEX_PROCESS_STATUS_FAILURE,
            'result' => strtolower(trim($message, ' .')),
            'finished_at' => $date->format('M j, Y, g:i A'),
            'error' => !$status_boolean,
            'error_message' => !$status_boolean ? 'Check doofinder panel for more information about the error.' : '',
        ];
    }

    /**
     * Validate process message
     *
     * @param string $message
     *
     * @return bool
     */
    private function validateProcessMessage(string $message): bool
    {
        return $message == 'Sources were processed successfully.' || $message == 'No changes in feeds.';
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        // Doofinder cannot send a form key; the callback token authenticates the request instead
        return true;
    }

    /**
     * Clean the config cache so the Doofinder Index Processing Status view shows the new status
     */
    private function cleanCache()
    {
        $this->cacheTypeList->cleanType(ConfigCache::TYPE_IDENTIFIER);
    }
}
