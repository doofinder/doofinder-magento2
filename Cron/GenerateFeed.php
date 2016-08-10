<?php

namespace Doofinder\Feed\Cron;

/**
 * Class GenerateFeed
 *
 * @package Doofinder\Feed\Cron
 */
class GenerateFeed
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * GenerateFeed constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
    }

    /**
     * Execute this cron job.
     *
     * @todo implement this method
     *
     * @return $this
     */
    public function execute()
    {
        $this->_logger->info('Doofinder cron execute');
        return $this;
    }

}