<?php

namespace Doofinder\Feed\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reschedule process command
 */
class RescheduleProcessCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Store argument
     */
    const STORE_ARGUMENT = 'store';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $schedule;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Symfony\Component\Console\Input\InputArgumentFactory
     */
    private $inputArgFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Magento\Framework\App\State $state
     * @param \Symfony\Component\Console\Input\InputArgumentFactory $inputArgFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\App\State $state,
        \Symfony\Component\Console\Input\InputArgumentFactory $inputArgFactory
    ) {
        $this->storeManager = $storeManager;
        $this->schedule = $schedule;
        $this->state = $state;
        $this->inputArgFactory = $inputArgFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @codingStandardsIgnoreStart
     */
    protected function configure()
    {
    // @codingStandardsIgnoreEnd
        $this->setName('doofinder:feed:process:reschedule')
            ->setDescription('Reschedule Doofinder Feed Process')
            ->addArgument(
                self::STORE_ARGUMENT,
                InputArgument::OPTIONAL,
                'Store'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @codingStandardsIgnoreStart
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    // @codingStandardsIgnoreEnd
        $this->state->setAreaCode('frontend');

        $store = $input->getArgument(self::STORE_ARGUMENT);
        if ($store === null) {
            throw new \InvalidArgumentException('Argument ' . self::STORE_ARGUMENT . ' is missing.');
        }

        $store = $this->storeManager->getStore($store);
        $this->schedule->updateProcess($store, true, true);
    }
}
