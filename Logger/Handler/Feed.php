<?php

namespace Doofinder\Feed\Logger\Handler;

use Monolog\Logger;

/**
 * Feed log handler
 */
class Feed extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * @var \Doofinder\Feed\Model\LogFactory
     */
    private $logFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $datetime;

    /**
     * @param \Doofinder\Feed\Model\LogFactory $logFactory
     * @param \Magento\Framework\Stdlib\DateTime $datetime
     */
    public function __construct(
        \Doofinder\Feed\Model\LogFactory $logFactory,
        \Magento\Framework\Stdlib\DateTime $datetime
    ) {
        $this->logFactory = $logFactory;
        $this->datetime = $datetime;
    }

    /**
     * Writes the record down as a Log model entry
     *
     * @param  array $record
     * @return void
     * @codingStandardsIgnoreStart
     */
    protected function write(array $record)
    {
    // @codingStandardsIgnoreEnd
        $logEntry = $this->logFactory->create();
        $logEntry->setData([
            'message' => $record['message'],
            'type' => strtolower($record['level_name']),
            'process_id' => $record['context']['process']->getId(),
            'time' => $this->datetime->formatDate($record['datetime']),
        ]);

        $logEntry->getResource()->save($logEntry);
    }
}
