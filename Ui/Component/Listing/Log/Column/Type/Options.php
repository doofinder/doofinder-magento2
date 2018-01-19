<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Doofinder\Feed\Ui\Component\Listing\Log\Column\Type;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Options UI component class
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Logger\Feed $logger
     */
    public function __construct(
        \Doofinder\Feed\Logger\Feed $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];

            foreach (array_values($this->logger->getLevelOptions()) as $name) {
                $name = strtolower($name);
                $this->options[] = [
                    'label' => $name,
                    'value' => $name,
                ];
            }
        }

        return $this->options;
    }
}
