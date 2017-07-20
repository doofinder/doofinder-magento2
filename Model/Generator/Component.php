<?php

namespace Doofinder\Feed\Model\Generator;

class Component extends \Magento\Framework\DataObject
{
    /**
     * @var \Psr\Log\LoggerInterface
     * @codingStandardsIgnoreStart
     */
    protected $_logger = null;
    // @codingStandardsIgnoreEnd

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_logger = $logger;
        parent::__construct($data);
    }
}
