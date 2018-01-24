<?php

namespace Doofinder\Feed\Logger;

/**
 * Feed logger
 */
class Feed extends \Monolog\Logger
{
    /**
     * @var \Doofinder\Feed\Model\Cron
     */
    private $process;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    private $logger;

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
        $this->process = $process;
        $this->logger = $logger;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record.
     *
     * @param  integer $level   The logging level.
     * @param  string  $message The log message.
     * @param  array   $context The log context.
     * @return void
     */
    public function addRecord($level, $message, array $context = [])
    {
        if (!isset($context['process'])) {
            $context['process'] = $this->process;
        }

        // Pass record to main logger
        $this->logger->addRecord($level, $message, $context);

        // Handle custom logging
        if (is_a($context['process'], \Doofinder\Feed\Model\Cron::class)) {
            parent::addRecord($level, $message, $context);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getLevelOptions()
    {
        return static::getLevels();
    }
}
