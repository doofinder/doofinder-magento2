<?php

namespace Doofinder\Feed\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;
use Doofinder\Feed\Block\Adminhtml\Form\Field\CustomAttributes\Enable;
use Doofinder\Feed\Block\Adminhtml\Form\Field\CustomAttributes\Code;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\Escaper;

/**
 * Class to manage custom attributes in the config section
 */
class CustomAttributes extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/customAttributes.phtml';

    /**
     * @var Code
     */
    protected $codeRenderer;

    /**
     * @var Enable
     */
    protected $enableRenderer;

    /**
     * @var StoreConfig
     */
    protected $helper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param Context $context
     * @param Code $codeRenderer
     * @param Enable $enableRenderer
     * @param StoreConfig $helper
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Code $codeRenderer,
        Enable $enableRenderer,
        StoreConfig $helper,
        Escaper $escaper
    ) {
        $this->codeRenderer = $codeRenderer;
        $this->enableRenderer = $enableRenderer;
        $this->helper = $helper;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    /**
     * Make Escaper available to the template
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('label', ['label' => __('Attribute'), 'renderer' => $this->codeRenderer]);
        $this->addColumn('code', ['label' => __('Code'), 'renderer' => $this->codeRenderer]);
        $this->addColumn('enabled', ['label' => __('Enabled'), 'renderer' => $this->enableRenderer]);

        parent::_construct();
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return mixed[]
     */
    public function getArrayRows()
    {
        $result = [];
        $values = $this->helper->getCustomAttributes();

        foreach ($values as $rowId => $row) {
            $row['_id'] = $rowId;
            $row['enabled'] = $row['enabled'] ? 'checked' : '';
            $result[$rowId] = new DataObject($row);
            $this->_prepareArrayRow($result[$rowId]);
        }
        return $result;
    }
}
