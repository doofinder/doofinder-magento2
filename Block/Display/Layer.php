<?php
declare(strict_types=1);


namespace Doofinder\Feed\Block\Display;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\View\Element\Template;

class Layer extends Template
{
    /** @var StoreConfig */
    private $storeConfig;

    public function __construct(
        StoreConfig $storeConfig,
        Template\Context $context,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get display layer
     *
     * @return string|null
     */
    public function getDisplayLayer(): ?string
    {
        return $this->storeConfig->getDisplayLayer();
    }
}
