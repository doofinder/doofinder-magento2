<?php

namespace Doofinder\Feed\Ui\Component\Listing\Log\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Store UI component
 */
class Store extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.store';

    /**
     * @var \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory
     */
    private $cronCollectionFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory $cronColFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Doofinder\Feed\Model\ResourceModel\Cron\CollectionFactory $cronColFactory,
        array $components = [],
        array $data = []
    ) {
        $this->cronCollectionFactory = $cronColFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $processes = $this->cronCollectionFactory->create()->getItems();

            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    if (isset($processes[$item[$fieldName]])) {
                        $item[$fieldName] = $processes[$item[$fieldName]]->getStoreCode();
                    } else {
                        $item[$fieldName] = __('Unknown');
                    }
                }
            }
        }

        return $dataSource;
    }
}
