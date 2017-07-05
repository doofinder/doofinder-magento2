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
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $_options;

    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $_logger;

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(
        \Doofinder\Feed\Logger\Feed $logger
    ) {
        $this->_logger = $logger;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [];

            foreach (array_values($this->_logger->getLevelOptions()) as $name) {
                $name = strtolower($name);
                $this->_options[] = [
                    'label' => $name,
                    'value' => $name,
                ];
            }
        }

        return $this->_options;
    }
}
