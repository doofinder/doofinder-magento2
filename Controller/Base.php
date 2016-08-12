<?php

namespace Doofinder\Feed\Controller;

/**
 * Abstract clas Base for Doofinder Feed Controlers.
 *
 * @package Doofinder\Feed\Controller
 */
abstract class Base extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * Base constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);

        $this->_jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Set content type as xml.
     *
     */
    protected function _setXmlHeaders()
    {
        $this->getResponse()->setHeader('Content-type', 'application/xml; charset="utf-8"', true);
    }

    /**
     * Set json headers.
     *
     * @param array $data
     *
     * @return mixed
     */
    protected function _setJsonHeaders(array $data = [])
    {
        return $this->_jsonResultFactory->create()
            ->setData($data);
    }

    /**
     * Get string param.
     *
     * @param $paramName
     * @return string|null
     */
    protected function getParamString($paramName)
    {
        return is_string($param = $this->getRequest()->getParam($paramName)) ? $param : null;
    }

    /**
     * Get int param.
     *
     * @param $paramName
     * @return int|null
     */
    protected function getParamInt($paramName)
    {
        return is_numeric($param = $this->getRequest()->getParam($paramName)) ? (int) $param : null;
    }
}