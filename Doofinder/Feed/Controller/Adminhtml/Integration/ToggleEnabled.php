<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\ScopeInterface;

class ToggleEnabled extends Action implements HttpPostActionInterface
{
    protected $jsonFactory;
    protected $configWriter;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        WriterInterface $configWriter
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->configWriter = $configWriter;
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $groupId = (int)$this->getRequest()->getParam('group');

        if (!$groupId) {
            return $resultJson->setData(['success' => false, 'message' => __('Group ID is required')]);
        }

        $this->configWriter->save(
            StoreConfig::DISPLAY_LAYER_ENABLED,
            (int)$this->getRequest()->getParam('enabled'),
            ScopeInterface::SCOPE_GROUP,
            $groupId
        );

        return $resultJson->setData(['success' => true]);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Doofinder_Doofinder::config');
    }
}
