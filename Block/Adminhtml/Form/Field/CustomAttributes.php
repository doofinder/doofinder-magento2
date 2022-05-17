<?php

namespace Doofinder\Feed\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;
use Doofinder\Feed\Block\Adminhtml\Form\Field\CustomAttributes\Enable;
use Doofinder\Feed\Block\Adminhtml\Form\Field\CustomAttributes\Code;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Class to manage custom attributes in the config section
 */
class CustomAttributes extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Doofinder_Feed::System/Config/customAttributes.phtml';
    protected $codeRenderer;
    protected $enableRenderer;
    protected $helper;

    public function __construct(
        Context $context,
        Code $codeRenderer,
        Enable $enableRenderer,
        StoreConfig $helper
    ) {
        $this->codeRenderer   = $codeRenderer;
        $this->enableRenderer = $enableRenderer;
        $this->helper = $helper;

        parent::__construct($context);
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
     * @return array
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
