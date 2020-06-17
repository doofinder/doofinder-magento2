<?php

namespace Doofinder\Feed\Test\Unit\Cron;

/**
 * Test class for \Doofinder\Feed\Helper\Indexer
 */
class IndexerTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $testedClass;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $indexerRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $search;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dimensionFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $indexer;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->storeConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\StoreConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexerRegistry = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->search = $this->getMockBuilder(\Doofinder\Feed\Helper\Search::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dimensionFactory = $this->getMockBuilder(\Magento\Framework\Search\Request\DimensionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexer = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testedClass = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\Indexer::class,
            [
                'storeConfig' => $this->storeConfig,
                'indexerRegistry' => $this->indexerRegistry,
                'search' => $this->search,
                'dimensionFactory' => $this->dimensionFactory,
            ]
        );
    }

    /**
     * @return void
     */
    public function testInvalidate()
    {
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())->method('invalidate');

        $this->testedClass->invalidate();
    }

    /**
     * @return void
     */
    public function testIsScheduled()
    {
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())->method('isScheduled')->willReturn(true);

        $this->assertTrue(
            $this->testedClass->isScheduled()
        );
    }

    /**
     * @param boolean $isSearchEnabled
     * @param boolean $isScheduled
     * @param boolean $result
     * @return void
     * @dataProvider isDelayedUpdatesEnabledDataProvider
     */
    public function testIsDelayedUpdatesEnabled($isSearchEnabled, $isScheduled, $result)
    {
        $this->storeConfig->expects($this->once())
            ->method('isInternalSearchEnabled')
            ->willReturn($isSearchEnabled);

        if ($isSearchEnabled) {
            $this->indexerRegistry->expects($this->once())
                ->method('get')
                ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
                ->willReturn($this->indexer);
            $this->indexer->expects($this->once())
                ->method('isScheduled')
                ->willReturn($isScheduled);
        }

        $this->assertEquals(
            $result,
            $this->testedClass->isDelayedUpdatesEnabled()
        );
    }

    /**
     * @return array
     */
    public function isDelayedUpdatesEnabledDataProvider()
    {
        return [
            [true, false, true],
            [true, true, false],
            [false, true, false],
            [false, false, false]
        ];
    }

    /**
     * @return void
     */
    public function testGetDimensions()
    {
        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeId = 1;
        $this->dimensionFactory->expects($this->once())
            ->method('create')
            ->with(
                ['name' => 'scope', 'value' => $storeId]
            )
            ->willReturn($dimension);

        $createdObject = $this->testedClass->getDimensions($storeId);
        $this->assertSame($dimension, $createdObject);

        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn($storeId);
        $this->assertSame($storeId, $createdObject->getValue());
    }
}
