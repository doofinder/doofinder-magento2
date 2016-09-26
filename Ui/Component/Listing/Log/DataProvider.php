<?php

namespace Doofinder\Feed\Ui\Component\Listing\Log;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $_dataPersistor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\Api\Search\ReportingInterface $reporting
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->_dataPersistor = $dataPersistor;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * Returns search criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteria
     */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria && ($process = $this->getProcess())) {
            $this->filterBuilder->setField('process_id');
            $this->filterBuilder->setValue($process->getId());
            $this->filterBuilder->setConditionType('eq');

            $this->addFilter($this->filterBuilder->create());
        }

        return parent::getSearchCriteria();
    }

    /**
     * Returns process
     *
     * @return \Doofinder\Feed\Model\Cron
     */
    protected function getProcess()
    {
        return $this->_dataPersistor->get('doofinder_feed_process');
    }
}
