<?php

namespace Doofinder\Feed\Logger\Handler;

use Monolog\Logger;

class Feed extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * @var \Doofinder\Feed\Model\LogFactory
     */
    protected $_logFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_datetime;

    /**
     * @param \Doofinder\Feed\Model\LogFactory $logFactory
     * @param integer $level
     * @param Boolean $bubble
     */
    public function __construct(
        \Doofinder\Feed\Model\LogFactory $logFactory,
        \Magento\Framework\Stdlib\DateTime $datetime
    ) {
        $this->_logFactory = $logFactory;
        $this->_datetime = $datetime;
    }

    /**
     * Writes the record down as a Log model entry
     *
     * @param  $record[]
     * @return void
     */
    protected function write(array $record)
    {
        $logEntry = $this->_logFactory->create();
        $logEntry->setData([
            'message' => $record['message'],
            'type' => strtolower($record['level_name']),
            'process_id' => $record['context']['process']->getId(),
            'time' => $this->_datetime->formatDate($record['datetime']),
        ]);

        $logEntry->getResource()->save($logEntry);
    }
}
