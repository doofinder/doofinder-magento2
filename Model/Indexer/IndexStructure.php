<?php

namespace Doofinder\Feed\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Doofinder\Feed\Model\Api\Indexer;

/**
 * Class IndexStructure
 * The class responsible for managing operations on Doofinder index schema
 */
class IndexStructure implements IndexStructureInterface
{
    const INDEX_NAME = 'product';

    /**
     * @var Indexer
     */
    private $api;

    /**
     * IndexStructure constructor.
     * @param Indexer $api
     */
    public function __construct(
        Indexer $api
    ) {
        $this->api = $api;
    }

    /**
     * @param string $index
     * @param array $dimensions
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($index, array $dimensions = [])
    {
        if ($this->api->isIndexExists(self::INDEX_NAME, $dimensions)) {
            $this->api->deleteDoofinderIndex($dimensions, self::INDEX_NAME);
        }
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        if (!$this->api->isIndexExists(self::INDEX_NAME, $dimensions)) {
            $this->api->createDoofinderIndex($dimensions, self::INDEX_NAME);
        }
        $this->api->createDoofinderIndexTemp($dimensions, self::INDEX_NAME);
    }
}
