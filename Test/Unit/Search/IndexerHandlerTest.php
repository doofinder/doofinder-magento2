<?php

namespace Doofinder\Feed\Test\Unit\Search;

class IndexerHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Search\IndexerHandler
     */
    private $_indexer;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\Batch
     */
    private $_batch;

    /**
     * @var \Traversable
     */
    private $_documents;

    /**
     * @var \Doofinder\Feed\Model\Generator
     */
    private $_generator;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $_generatorFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $_product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $_productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $_productCollectionFactory;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $_feedConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_searchHelper;

    /**
     * @var \Magento\Framework\Search\Request\Dimension
     */
    private $_dimension;

    /**
     * @var \Magento\Framework\Indexer\IndexStructureInterface
     */
    private $_indexStructure;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $_connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_resource;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $_objectManagerHelper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_documents = new \ArrayObject([['sample' => 'item']]);

        $this->_batch = $this->getMock(
            '\Magento\Framework\Indexer\SaveHandler\Batch',
            [],
            [],
            '',
            false
        );

        $this->_generator = $this->getMock(
            '\Doofinder\Feed\Model\Generator',
            [],
            [],
            '',
            false
        );

        $this->_generatorFactory = $this->getMock(
            '\Doofinder\Feed\Model\GeneratorFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->_productCollection = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection',
            [],
            [],
            '',
            false
        );
        $this->_productCollection->method('getItems')->willReturn([$this->_product]);

        $this->_productCollectionFactory = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_productCollectionFactory->method('create')->willReturn($this->_productCollection);

        $this->_feedConfig = $this->getMock(
            '\Doofinder\Feed\Helper\FeedConfig',
            [],
            [],
            '',
            false
        );
        $this->_feedConfig->method('getLeanFeedConfig')->with('sample')->willReturn([
            'data' => [
                'config' => [
                    'processors' => [
                        'Mapper' => [
                            'map' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $this->_dimension = $this->getMock(
            '\Magento\Framework\Search\Request\Dimension',
            [],
            [],
            '',
            false
        );
        $this->_dimension->method('getName')->willReturn('scope');
        $this->_dimension->method('getValue')->willReturn('sample');

        $this->_searchHelper = $this->getMock(
            '\Doofinder\Feed\Helper\Search',
            [],
            [],
            '',
            false
        );
        $this->_searchHelper->method('cleanDoofinderItems')->willReturn(true);
        $this->_searchHelper->method('getStoreIdFromDimensions')
            ->with([$this->_dimension])->willReturn('sample');

        $this->_indexStructure = $this->getMock(
            '\Magento\Framework\Indexer\IndexStructureInterface',
            [],
            [],
            '',
            false
        );

        $this->_connection = $this->getMock(
            '\Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            [],
            '',
            false
        );

        $this->_resource = $this->getMock(
            '\Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false
        );
        $this->_resource->method('getConnection')->willReturn($this->_connection);

        $this->_indexer = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Search\IndexerHandler',
            [
                'batch' => $this->_batch,
                'generatorFactory' => $this->_generatorFactory,
                'productCollectionFactory' => $this->_productCollectionFactory,
                'feedConfig' => $this->_feedConfig,
                'searchHelper' => $this->_searchHelper,
                'indexStructure' => $this->_indexStructure,
                'resource' => $this->_resource,
                'data' => [
                    'indexer_id' => 'sample-indexer',
                    'fieldsets' => [],
                ],
            ]
        );
    }

    /**
     * Test saveIndex() method.
     */
    public function testSaveIndex()
    {
        $this->_batch->expects($this->exactly(2))->method('getItems')
            ->with($this->_documents, 100)->willReturn([$this->_documents->getArrayCopy()]);

        $this->_generator->expects($this->once())->method('run');

        $this->_generatorFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'config' => [
                        'fetchers' => [
                            'Product\Fixed' => [
                                'products' => [$this->_product],
                            ],
                        ],
                        'processors' => [
                            'AtomicUpdater' => [
                                'action' => 'update',
                            ],
                            'Mapper' => [
                                'map' => [],
                            ],
                        ],
                    ],
                ],
            ])
            ->willReturn($this->_generator);

        $this->_indexer->saveIndex([$this->_dimension], $this->_documents);
    }

    /**
     * Test deleteIndex() method.
     */
    public function testDeleteIndex()
    {
        $this->_batch->expects($this->exactly(2))->method('getItems')
            ->with($this->_documents, 100)->willReturn([$this->_documents->getArrayCopy()]);

        $this->_generator->expects($this->once())->method('run');

        $this->_generatorFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'config' => [
                        'fetchers' => [
                            'Product\Fixed' => [
                                'products' => [$this->_product],
                            ],
                        ],
                        'processors' => [
                            'AtomicUpdater' => [
                                'action' => 'delete',
                            ],
                            'Mapper' => [
                                'map' => [],
                            ],
                        ],
                    ],
                ],
            ])
            ->willReturn($this->_generator);

        $this->_indexer->deleteIndex([$this->_dimension], $this->_documents);
    }

    /**
     * Test cleanIndex() method.
     */
    public function testCleanIndex()
    {
        $this->_indexStructure->expects($this->once())->method('delete')
            ->with('sample-indexer', [$this->_dimension]);
        $this->_indexStructure->expects($this->once())->method('create')
            ->with('sample-indexer', [], [$this->_dimension]);

        $this->_indexer->cleanIndex([$this->_dimension]);
    }

    /**
     * Test isAvailable() method.
     */
    public function testIsAvailable()
    {
        $this->assertTrue($this->_indexer->isAvailable());
    }
}
