<?php

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();


/** @var \Magento\Framework\Stdlib\DateTime $dateTime */
$dateTime = $objectManager->get(\Magento\Framework\Stdlib\DateTime::class);

/** @var \Doofinder\Feed\Model\Cron $process */
$process = $objectManager->create(\Doofinder\Feed\Model\Cron::class);

$process
    ->setStoreCode('default')
    ->setStatus($process::STATUS_WAITING)
    ->setMessage($process::MSG_SUCCESS)
    ->setErrorStack(0)
    ->setCreatedAt($dateTime->formatDate(time()))
    ->setComplete($dateTime->formatDate(time()))
    ->setNextRun('-')
    ->setNextIteration('-')
    ->setLastFeedName('doofinder-default.xml');

$process->save();
