<?php
declare(strict_types=1);


namespace Doofinder\Feed\Helper;

use Doofinder\Feed\ApiClient\ManagementClient;
use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Errors\BadRequest;
use Doofinder\Feed\Errors\IndexingInProgress;
use Doofinder\Feed\Errors\NotAllowed;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Errors\QuotaExhausted;
use Doofinder\Feed\Errors\ThrottledResponse;
use Doofinder\Feed\Errors\TypeAlreadyExists;
use Doofinder\Feed\Errors\WrongResponse;
use Doofinder\Feed\Wrapper\ThrottleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Indice extends AbstractHelper
{
    const MAGENTO_INDICE_NAME = 'magento2';

    /** @var ManagementClientFactory  */
    private $managementClientFactory;

    /** @var ThrottleFactory */
    private $throttleFactory;

    /** @var string|null  */
    private $apiKey;

    public function __construct(
        ManagementClientFactory $managementClientFactory,
        ThrottleFactory $throttleFactory,
        Context $context,
        string $apiKey = null
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->throttleFactory  = $throttleFactory;
        $this->apiKey = $apiKey;
        parent::__construct($context);
    }

    /**
     * Create an indice
     *
     * @param array $indice
     * @param string $hashId
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function createIndice(array $indice, string $hashId): array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);

        return $managementClient->createIndice($indice, $hashId);
    }

    /**
     * Checks if Magento indice exists in search engine
     *
     * @param array $searchEngine
     * @param string $name
     * @return array
     * @throws NotFound
     */
    public function getIndiceFromSearchEngine(array $searchEngine, string $name): array
    {
        $indice = null;
        foreach ($searchEngine['indices'] as $i) {
            if ($i['name'] == $name) {
                $indice = $i;
            }
        }
        if ($indice === null) {
            throw new NotFound(
                sprintf("Indice '%s' doesn't exist in search engine '%s'", $name, $searchEngine['name'])
            );
        }

        return $indice;
    }
}
