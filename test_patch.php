<?php
/**
 * Test script to manually run NotifySingleScriptMigrationPatch
 * 
 * Usage: docker compose exec -u application web php test_patch.php
 * Or: make dev-console, then: php test_patch.php
 */

use Magento\Framework\App\Bootstrap;
use Doofinder\Feed\Setup\Patch\Data\NotifySingleScriptMigrationPatch;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

require __DIR__ . '/app/bootstrap.php';

// Create a console logger that outputs to stdout
class ConsoleLogger implements LoggerInterface
{
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_PRETTY_PRINT) : '';
        echo "[$timestamp] [$level] $message$contextStr\n";
    }
}

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

// Get required dependencies
$moduleDataSetup = $objectManager->get(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
$clientFactory = $objectManager->get(\Doofinder\Feed\ApiClient\ClientFactory::class);
$storeConfig = $objectManager->get(\Doofinder\Feed\Helper\StoreConfig::class);
$groupCollectionFactory = $objectManager->get(\Magento\Store\Model\ResourceModel\Group\CollectionFactory::class);

// Use console logger instead of default logger to see output
$logger = new ConsoleLogger();

// Instantiate the patch
$patch = new NotifySingleScriptMigrationPatch(
    $moduleDataSetup,
    $clientFactory,
    $storeConfig,
    $groupCollectionFactory,
    $logger
);

// Add debug output to see what's happening
echo "========================================\n";
echo "Running NotifySingleScriptMigrationPatch\n";
echo "========================================\n\n";

// Debug: Check how many store groups exist
$groupCollection = $groupCollectionFactory->create();
$totalGroups = $groupCollection->getSize();
echo "Found $totalGroups store group(s)\n\n";

// Debug: Check installation IDs using the helper method
foreach ($groupCollection as $group) {
    $storeGroupId = (int)$group->getId();
    $installationId = $storeConfig->getInstallationId($storeGroupId);
    echo "Store Group ID: $storeGroupId, Installation ID: " . ($installationId ?: 'NOT SET') . "\n";
}
echo "\n";

// Run the patch
try {
    $patch->apply();
    echo "\n========================================\n";
    echo "Patch executed successfully!\n";
    echo "========================================\n";
} catch (\Exception $e) {
    echo "\n========================================\n";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    echo "========================================\n";
}

