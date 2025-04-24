<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Cron\Processor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class UpdateOnClick extends Action
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * UpdateOnClick constructor.
     *
     * @param Processor $processor
     * @param Context $context
     */
    public function __construct(
        Processor $processor,
        Context $context
    ) {
        $this->processor = $processor;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->processor->execute();
    }
}
