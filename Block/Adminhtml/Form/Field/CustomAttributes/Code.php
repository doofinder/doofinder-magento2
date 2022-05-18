<?php
namespace Doofinder\Feed\Block\Adminhtml\Form\Field\CustomAttributes;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Backend system config field renderer
 */
class Code extends AbstractBlock
{
    protected function _toHtml()
    {
        $id = $this->getInputId();
        $iName = $this->getInputName();
        $cName = $this->getColumnName();
        $column = $this->getColumn();

        $class = isset($column['class']) ? $column['class'] : 'input-text';
        $style = isset($column['style']) ? $column['style'] : '';

        return "<input type=\"hidden\" id=\"$id\" value=\"<%- $cName %>\" name=\"$iName\"/>" . 
               "<span class=\"productsorting_code $class\" style=\"$style\"><%- $cName %></span>";
    }
}
