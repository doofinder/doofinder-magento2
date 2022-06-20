<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class CleanIntegration extends Action
{

    private $integration_table_column = ['integration' => 'name', 'core_config_data' => 'path'];

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
            foreach ($this->integration_table_column as $table => $column) {
                $this->delete_integration_entries($connection, $table, $column);
            } 
        } catch (Exception $e) {
            $this->logger->error('There was a problem cleaning the database from Doofinder entries: ' . $e->getMessage());
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Doofinder_Feed::config');
    }

    private function delete_integration_entries($connection, $table, $column) {
        $integrationTable = $this->resourceConnection->getTableName($table);
        $connection->delete(
            $integrationTable,
            $column.' like "%doofinder%"'
        );
    }
}
