<?php

namespace Doofinder\Feed\Block\Adminhtml\Form\Field;

/**
 * Class AdditionalAttributes
 *
 * @package Doofinder\Feed\Block\Adminhtml\Form\Field
 */
class AdditionalAttributes extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Model Feed Attributes.
     *
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    protected $_feedAttributes;

    /**
     * AdditionalAttributes constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Doofinder\Feed\Model\Config\Source\Feed\Attributes $feedAttributes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Doofinder\Feed\Model\Config\Source\Feed\Attributes $feedAttributes,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_feedAttributes = $feedAttributes;
    }

    /**
     * @param string $value
     * @return Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Return html code for select.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $attributes = $this->_feedAttributes->getAllAttributes();

            foreach ($attributes as $code => $label) {
                $this->addOption($code, $label);
            }
        }

        return parent::_toHtml();
    }
}
