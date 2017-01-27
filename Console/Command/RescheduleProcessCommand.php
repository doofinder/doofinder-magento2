<?php

namespace Doofinder\Feed\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RescheduleProcessCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Store argument
     */
    const STORE_ARGUMENT = 'store';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\Schedule $schedule,
        \Magento\Framework\App\State $state
    ) {
        $this->_storeManager = $storeManager;
        $this->_schedule = $schedule;
        $this->_state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('doofinder:feed:process:reschedule')
            ->setDescription('Reschedule Doofinder Feed Process')
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

        $store = $this->_storeManager->getStore($store);
        $this->_schedule->updateProcess($store, true, true);
    }
}
