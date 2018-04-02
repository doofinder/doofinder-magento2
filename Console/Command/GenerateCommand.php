<?php

namespace Doofinder\Feed\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate command
 */
class GenerateCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Store argument
     */
    const STORE_ARGUMENT = 'store';

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Symfony\Component\Console\Input\InputArgumentFactory
     */
    private $inputArgFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Magento\Framework\App\State $state
     * @param \Symfony\Component\Console\Input\InputArgumentFactory $inputArgFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Magento\Framework\App\State $state,
        \Symfony\Component\Console\Input\InputArgumentFactory $inputArgFactory
    ) {
        $this->storeManager = $storeManager;
        $this->feedConfig = $feedConfig;
        $this->generatorFactory = $generatorFactory;
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
        $this->setName('doofinder:feed:generate')
            ->setDescription('Generate feed XML')
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

        $storeCode = $input->getArgument(self::STORE_ARGUMENT);
        if ($storeCode === null) {
            throw new \InvalidArgumentException('Argument ' . self::STORE_ARGUMENT . ' is missing.');
        }

        // Set current store for generator
        $this->storeManager->setCurrentStore($storeCode);

        $feedConfig = $this->feedConfig->getFeedConfig($storeCode);

        // Create generator
        $generator = $this->generatorFactory->create($feedConfig);

        // Run generator
        $generator->run();

        $xml = $generator->getProcessor('Xml');
        $output->write($xml->getFeed());
    }
}
