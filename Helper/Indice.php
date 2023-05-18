<?php
declare(strict_types=1);


namespace Doofinder\Feed\Helper;

use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Wrapper\ThrottleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Indice extends AbstractHelper
{
    const MAGENTO_INDICE_NAME = 'product';

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
     * Checks if Magento indice exists in search engine
     *
     * @param array $searchEngine
     * @param string $name
     * @return $indiceExists
     * @throws NotFound
     */
    public function checkIndiceExistsInSearchEngine(array $searchEngine, string $name): bool
    {
        $indiceExists = false;
        foreach ($searchEngine['indices'] as $i) {
            if ($i['name'] == $name) {
                $indiceExists = true;
                break;
            }
        }
        return $indiceExists;
    }
}
