<?php

namespace Doofinder\Feed\Controller\Feed;

/**
 * Class Index
 *
 * @package Doofinder\Feed\Controller\Feed
 */
class Index extends \Doofinder\Feed\Controller\Base
{
    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helperData;
    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    protected $_generatorFactory;
    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_feedConfig;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Doofinder\Feed\Helper\Data $helperData
     * @param \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Doofinder\Feed\Helper\Data $helperData,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig
    ) {
        parent::__construct($context, $jsonResultFactory);

        $this->_helperData = $helperData;
        $this->_generatorFactory = $generatorFactory;
        $this->_feedConfig = $feedConfig;
    }

    /**
     * Exexute. Return feed with xml content type.
     *
     */
    public function execute()
    {
        $this->_setXmlHeaders();

        $feedConfig = $this->_feedConfig->getFeedConfig();

        $generator = $this->_generatorFactory->create($feedConfig);

        $generator->run();

        $feed = $generator->getProcessor('Xml')->getFeed();

        $this->getResponse()->setBody($feed);
    }
}
