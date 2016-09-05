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
    protected $_cronFactory;

    /**
     * @var \Doofinder\Feed\Helper\Schedule
     */
    protected $_schedule;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        \Doofinder\Feed\Model\CronFactory $cronFactory,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\App\State $state
    ) {
        $this->_cronFactory = $cronFactory;
        $this->_schedule = $schedule;
        $this->_state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('doofinder:feed:process:run')
            ->setDescription('Run Doofinder Feed Process')
            ->setDefinition([
                new InputArgument(
                    self::STORE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Store'
                ),
            ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode('frontend');

        $store = $input->getArgument(self::STORE_ARGUMENT);
        if (is_null($store)) {
            throw new \InvalidArgumentException('Argument ' . self::STORE_ARGUMENT . ' is missing.');
        }

        $process = $this->_cronFactory->create()->load($store, 'store_code');
        $this->_schedule->runProcess($process);
    }
}
