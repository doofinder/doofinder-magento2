<?php

namespace Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class DataProviderTest
 * @package Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log
 */
class DataProviderTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $_dataPersistor;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $_filter;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria
     */
    private $_searchCriteria;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var \Doofinder\Feed\Ui\Component\Listing\Log\DataProvider
     */
    private $_dataProvider;

    public function setUp()
    {
        parent::setUp();

        $this->_filter = $this->getMock(
            '\Magento\Framework\Api\Filter',
            [],
            [],
            '',
            false
        );

        $this->_filterBuilder = $this->getMock(
            '\Magento\Framework\Api\FilterBuilder',
            [],
            [],
            '',
            false
        );
        $this->_filterBuilder->method('create')->willReturn($this->_filter);

        $this->_searchCriteria = $this->getMock(
            '\Magento\Framework\Api\Search\SearchCriteria',
            [],
            [],
            '',
            false
        );

        $this->_searchCriteriaBuilder = $this->getMock(
            '\Magento\Framework\Api\Search\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->_searchCriteriaBuilder->method('create')->willReturn($this->_searchCriteria);

        $this->_dataPersistor = $this->getMock(
            '\Magento\Framework\App\Request\DataPersistorInterface',
            [],
            [],
            '',
            false
        );

        $this->_dataProvider = $this->objectManager->getObject(
            '\Doofinder\Feed\Ui\Component\Listing\Log\DataProvider',
            [
                'filterBuilder' => $this->_filterBuilder,
                'searchCriteriaBuilder' => $this->_searchCriteriaBuilder,
                'dataPersistor' => $this->_dataPersistor,
            ]
        );
    }

    /**
     * Test getSearchCriteria()
     */
    public function testGetSearchCriteria()
    {
        $process = $this->getMock(
            '\Doofinder\Feed\Model\Cron',
            [],
            [],
            '',
            false
        );
        $process->method('getId')->willReturn(5);

        $this->_dataPersistor->expects($this->once())->method('get')
            ->with('doofinder_feed_process')->willReturn($process);

        $this->_filterBuilder->expects($this->once())->method('setField')
            ->with('process_id');
        $this->_filterBuilder->expects($this->once())->method('setValue')
            ->with(5);
        $this->_filterBuilder->expects($this->once())->method('setConditionType')
            ->with('eq');

        $this->_searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with($this->_filter);

        $this->_dataProvider->getSearchCriteria();
    }

    /**
     * Test getSearchCriteria() without process
     */
    public function testGetSearchCriteriaWithoutProcess()
    {
        $this->_searchCriteriaBuilder->expects($this->never())->method('addFilter')
            ->with($this->_filter);

        $this->_dataProvider->getSearchCriteria();
    }
}
