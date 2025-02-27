<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Cron\Processor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class UpdateOnClick extends Action
{
    private $processor;

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
