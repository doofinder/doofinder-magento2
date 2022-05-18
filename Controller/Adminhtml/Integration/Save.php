<?php
declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Magento\Integration\Controller\Adminhtml\Integration\Save as IntegrationSave;

/**
 * Integration Save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends IntegrationSave
{
    /**
     * We ignore to validate the current user password
     *
     * @return bool
     */
    protected function validateUser(): bool
    {
        return true;
    }
}
