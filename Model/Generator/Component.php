<?php

namespace Doofinder\Feed\Model\Generator;

/**
 * Component
 */
class Component extends \Magento\Framework\DataObject
{
    /**
     * @var \Psr\Log\LoggerInterface
     * @codingStandardsIgnoreStart
     */
    protected $logger = null;
    // @codingStandardsIgnoreEnd

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($data);
    }
}
