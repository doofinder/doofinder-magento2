<?php

namespace Doofinder\Feed\Observer\Processor\Xml;

use \Magento\Framework\Event\ObserverInterface;

class ItemsFetched implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($xmlProcessor = $observer->getGenerator()->getProcessor('Xml')) {

            $isStart = true;
            $isEnd = true;

            foreach ($observer->getGenerator()->getFetcher() as $fetcher) {
                $isStart = $isStart && $fetcher->isStarted();
                $isEnd = $isEnd && $fetcher->isDone();
            }

            $xmlProcessor->setStart($isStart);
            $xmlProcessor->setEnd($isEnd);
        }
    }
}
