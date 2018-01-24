<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Dynamic feed controller
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $feedConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInteface
     */
    private $storeManager;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Doofinder\Feed\Helper\Data $helper
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     * @param \Doofinder\Feed\Helper\FeedConfig $feedConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Doofinder\Feed\Helper\Data $helper,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->generatorFactory = $generatorFactory;
        $this->feedConfig = $feedConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns feed with xml content type.
     *
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException Unauthorized access.
     */
    public function execute()
    {
        $storeCode = $this->helper->getParamString('store');

        // Do not proceed if password check fails
        if (!$this->checkPassword($storeCode)) {
            throw new \Magento\Framework\Exception\NotFoundException(
                __('Unauthorized access to feed.')
            );
        }

        $feedConfig = $this->feedConfig->getFeedConfig($storeCode, $this->getFeedCustomParams());

        // Set current store for generator
        if ($storeCode) {
            $this->storeManager->setCurrentStore($storeCode);
        }

        // Enforce transforming offset to last processed entity id
        $feedConfig['data']['config']['fetchers']['Product']['transform_offset'] = true;

        $generator = $this->generatorFactory->create($feedConfig);
        $generator->run();

        $feed = $generator->getProcessor('Xml')->getFeed();

        $this->helper->setXmlHeaders($this->getResponse());
        $this->getResponse()->setBody($feed);
    }

    /**
     * Get custom params for generator.
     *
     * @return array
     */
    private function getFeedCustomParams()
    {
        $params = [
            'offset' => $this->helper->getParamInt('offset'),
            'limit' => $this->helper->getParamInt('limit'),
        ];

        return array_filter($params, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Check password
     *
     * @param string $storeCode
     * @return boolean
     */
    private function checkPassword($storeCode)
    {
        $password = $this->feedConfig->getFeedPassword($storeCode);
        return !$password || $this->helper->getParamString('password') == $password;
    }
}
