<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Create display layers
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CleanIntegration extends Action
{

    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();
        try {
            $this->delete_integration_table_entries($connection);
            $this->delete_config_table_entries($connection);
        } catch (Exception $e) {
            $this->logger->error('There was a problem cleaning the database from Doofinder entries: ' . $e->getMessage());
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Doofinder_Feed::config');
    }

    private function delete_integration_table_entries($connection) {
        $integrationTable = $this->resourceConnection->getTableName('integration');
        $connection->delete(
            $integrationTable,
            'name like "%doofinder%"'
        );
    }

    private function delete_config_table_entries($connection) {
        $integrationTable = $this->resourceConnection->getTableName('core_config_data');
        $connection->delete(
            $integrationTable,
            'path like "%doofinder%"'
        );
    }
}
