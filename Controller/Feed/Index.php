<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Class Index
 *
 * @package Doofinder\Feed\Controller\Feed
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $_helper;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    private $_generatorFactory;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $_feedConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInteface
     */
    private $_storeManager;

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

        $this->_helper = $helper;
        $this->_generatorFactory = $generatorFactory;
        $this->_feedConfig = $feedConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Execute. Return feed with xml content type.
     */
    public function execute()
    {
        $storeCode = $this->_helper->getParamString('store');

        // Do not proceed if password check fails
        if (!$this->checkPassword($storeCode)) {
            throw new \Magento\Framework\Exception\NotFoundException(
                __('Unauthorized access to feed.')
            );
        }

        $feedConfig = $this->_feedConfig->getFeedConfig($storeCode, $this->getFeedCustomParams());

        // Set current store for generator
        if ($storeCode) {
            $this->_storeManager->setCurrentStore($storeCode);
        }

        // Enforce transforming offset to last processed entity id
        $feedConfig['data']['config']['fetchers']['Product']['transform_offset'] = true;

        $generator = $this->_generatorFactory->create($feedConfig);
        $generator->run();

        $feed = $generator->getProcessor('Xml')->getFeed();

        $this->_helper->setXmlHeaders($this->getResponse());
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
            'offset' => $this->_helper->getParamInt('offset'),
            'limit' => $this->_helper->getParamInt('limit'),
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
        $password = $this->_feedConfig->getFeedPassword($storeCode);
        return !$password || $this->_helper->getParamString('password') == $password;
    }
}
