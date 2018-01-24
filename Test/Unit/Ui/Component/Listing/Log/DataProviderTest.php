<?php

namespace Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Magento\Framework\App\Request\DataPersistorInterface
 */
class DataProviderTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $filter;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria
     */
    private $searchCriteria;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Doofinder\Feed\Ui\Component\Listing\Log\DataProvider
     */
    private $dataProvider;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->filter = $this->getMock(
            \Magento\Framework\Api\Filter::class,
            [],
            [],
            '',
            false
        );

        $this->filterBuilder = $this->getMock(
            \Magento\Framework\Api\FilterBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->filterBuilder->method('create')->willReturn($this->filter);

        $this->searchCriteria = $this->getMock(
            \Magento\Framework\Api\Search\SearchCriteria::class,
            [],
            [],
            '',
            false
        );

        $this->searchCriteriaBuilder = $this->getMock(
            \Magento\Framework\Api\Search\SearchCriteriaBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilder->method('create')->willReturn($this->searchCriteria);

        $this->dataPersistor = $this->getMock(
            \Magento\Framework\App\Request\DataPersistorInterface::class,
            [],
            [],
            '',
            false
        );

        $this->dataProvider = $this->objectManager->getObject(
            \Doofinder\Feed\Ui\Component\Listing\Log\DataProvider::class,
            [
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'dataPersistor' => $this->dataPersistor,
            ]
        );
    }

    /**
     * Test getSearchCriteria() method
     *
     * @return void
     */
    public function testGetSearchCriteria()
    {
        $process = $this->getMock(
            \Doofinder\Feed\Model\Cron::class,
            [],
            [],
            '',
            false
        );
        $process->method('getId')->willReturn(5);

        $this->dataPersistor->expects($this->once())->method('get')
            ->with('doofinder_feed_process')->willReturn($process);

        $this->filterBuilder->expects($this->once())->method('setField')
            ->with('process_id');
        $this->filterBuilder->expects($this->once())->method('setValue')
            ->with(5);
        $this->filterBuilder->expects($this->once())->method('setConditionType')
            ->with('eq');

        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with($this->filter);

        $this->dataProvider->getSearchCriteria();
    }

    /**
     * Test getSearchCriteria() without process
     *
     * @return void
     */
    public function testGetSearchCriteriaWithoutProcess()
    {
        $this->searchCriteriaBuilder->expects($this->never())->method('addFilter')
            ->with($this->filter);

        $this->dataProvider->getSearchCriteria();
    }
}
