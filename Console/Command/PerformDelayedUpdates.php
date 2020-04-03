<?php

namespace Doofinder\Feed\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doofinder\Feed\Helper\Indexer as IndexerHelper;
use Doofinder\Feed\Model\ChangedProduct\Processor;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

/**
 * Class PerformDelayedUpdates
 * This class reflects current product data in Doofinder on command run.
 */
class PerformDelayedUpdates extends Command
{
    const ARG_FORCE = 'force';

    /**
     * @var IndexerHelper
     */
    private $helper;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var State
     */
    private $state;

    /**
     * PerformDelayedUpdates constructor.
     * @param IndexerHelper $helper
     * @param Processor $processor
     * @param State $state
     * @param mixed $name
     */
    public function __construct(IndexerHelper $helper, Processor $processor, State $state, $name = null)
    {
        $this->helper = $helper;
        $this->processor = $processor;
        $this->state = $state;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('doofinder:indexer:delayed_updates')
            ->setDescription('Execute Delayed Updates')
            ->addOption(
                self::ARG_FORCE,
                'f',
                InputOption::VALUE_OPTIONAL,
                'Force execute even if Delayed Updates are disabled. Usage: --force=1',
                0
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws LocalizedException If Delayed updates disabled.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $force = (bool) $input->getOption(self::ARG_FORCE);
        if (!$this->helper->isDelayedUpdatesEnabled() && !$force) {
            throw new LocalizedException(__('Delayed updates are disabled. Use --force=1 to proceed'));
        }

        $output->writeln('<info>Started</info>');

        $self = $this;
        $this->state->emulateAreaCode(Area::AREA_FRONTEND, function () use ($self) {
            $self->processor->execute();
        });

        $output->writeln('<info>Finished</info>');
    }
}
