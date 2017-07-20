<?php

namespace Doofinder\Feed\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunProcessCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Store argument
     */
    const STORE_ARGUMENT = 'store';

    /**
     * @var \Doofinder\Feed\Model\CronFactory
     */
    private $_cronFactory;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    private $_schedule;

    /**
     * @var \Magento\Framework\App\State
     */
    private $_state;

    /**
     * @var \Symfony\Component\Console\Input\InputArgumentFactory
     */
    private $_inputArgFactory;

    /**
     * @param \Doofinder\Feed\Model\CronFactory $cronFactory
     * @param \Doofinder\Feed\Helper\Schedule $schedule
     * @param \Magento\Framework\App\State $state
     * @param \Symfony\Component\Console\Input\InputArgumentFactory $inputArgFactory
     */
    public function __construct(
        \Doofinder\Feed\Model\CronFactory $cronFactory,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\App\State $state,
        \Symfony\Component\Console\Input\InputArgumentFactory $inputArgFactory
    ) {
        $this->_cronFactory = $cronFactory;
        $this->_schedule = $schedule;
        $this->_state = $state;
        $this->_inputArgFactory = $inputArgFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @codingStandardsIgnoreStart
     */
    protected function configure()
    {
    // @codingStandardsIgnoreEnd
        $this->setName('doofinder:feed:process:run')
            ->setDescription('Run Doofinder Feed Process')
            ->addArgument(
                self::STORE_ARGUMENT,
                InputArgument::OPTIONAL,
                'Store'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @codingStandardsIgnoreStart
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    // @codingStandardsIgnoreEnd
        $this->_state->setAreaCode('frontend');

        $store = $input->getArgument(self::STORE_ARGUMENT);
        if ($store === null) {
            throw new \InvalidArgumentException('Argument ' . self::STORE_ARGUMENT . ' is missing.');
        }

        $process = $this->_cronFactory->create()->load($store, 'store_code');
        $this->_schedule->runProcess($process);
    }
}
