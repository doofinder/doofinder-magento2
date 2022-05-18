<?php
declare(strict_types=1);

namespace Doofinder\Feed\Model\Config\Backend;

use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Errors\DoofinderFeedException;
use Doofinder\Feed\Errors\NotAllowed;
use Doofinder\Feed\Helper\SearchEngineFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * API key validation backend
 */
class ApiKeyValidation extends Value
{
    /**
     * @var SearchEngineFactory
     */
    private $searchEngineFactory;

    /**
     * ApiKeyValidation constructor.
     *
     * @param SearchEngineFactory $searchEngineFactory
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        SearchEngineFactory $searchEngineFactory,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->searchEngineFactory = $searchEngineFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check API Key is valida before save.
     *
     * @return ApiKeyValidation
     * @throws DoofinderFeedException
     * @throws ValidatorException
     */
    public function beforeSave(): ApiKeyValidation
    {
        if ($apiKey = $this->getValue()) {
            if (!preg_match('/^(us1|eu1)-[0-9a-f]{40}$/', $apiKey)) {
                throw new ValidatorException(
                    __('API key %1 is in an invalid format.', $apiKey)
                );
            }
            try {
                $searchEngine = $this->searchEngineFactory->create(['apiKey' => $apiKey]);
                $searchEngine->listSearchEngines();
            } catch (NotAllowed $e) {
                throw new ValidatorException(
                    __('API key %1 is invalid.', $apiKey)
                );
            } catch (\Exception $e) {
                throw new DoofinderFeedException($e->getMessage());
            }
        }

        return parent::beforeSave();
    }
}
