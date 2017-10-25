<?php

namespace Doofinder\Feed\Logger;

class Feed extends \Monolog\Logger
{
    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $_process;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    private $_logger;

    /**
     * @param \Doofinder\Feed\Model\Cron $process
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $name
     * @param HandlerInterface[] $handlers
     * @param callable[] $processors
     */
    public function __construct(
        \Doofinder\Feed\Model\Cron $process,
        \Psr\Log\LoggerInterface $logger,
        $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->_process = $process;
        $this->_logger = $logger;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record.
     *
     * @param  integer $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [])
    {
        if (!isset($context['process'])) {
            $context['process'] = $this->_process;
        }

        // Pass record to main logger
        $this->_logger->addRecord($level, $message, $context);

        // Handle custom logging
        if (is_a($context['process'], '\Doofinder\Feed\Model\Cron')) {
            parent::addRecord($level, $message, $context);
        }
    }

    public function getLevelOptions()
    {
        return static::getLevels();
    }
}
