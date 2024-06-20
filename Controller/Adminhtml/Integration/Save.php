<?php

declare(strict_types=1);

namespace Doofinder\Feed\Controller\Adminhtml\Integration;

use Magento\Integration\Controller\Adminhtml\Integration\Save as IntegrationSave;

/**
 * Integration Save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends IntegrationSave {
    /**
     * If the integration is ours, ignore the current user password validation
     *
     * @return $this
     */
    protected function validateUser() {
        $integration_name = $this->getRequest()->getParam("name");
        $is_doofinder_integration = $this->getRequest()->getParam("is_doofinder_integration") === "true" ? true : false;
        if ($is_doofinder_integration && $integration_name === "Doofinder Integration") {
            return $this;
        }

        return parent::validateUser();
    }
}
