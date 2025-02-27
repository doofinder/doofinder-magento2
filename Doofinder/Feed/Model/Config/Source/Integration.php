<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Integration\Model\ResourceModel\Integration\Collection;
use Magento\Integration\Model\ResourceModel\Integration\CollectionFactory;

class Integration implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory->create()->addFieldToFilter('name', 'Doofinder Integration');
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $optionArray = [];
        foreach ($this->collection->getItems() as $item) {
            /** @var \Magento\Integration\Model\Integration $item */
            $optionArray[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }

        return $optionArray;
    }
}
