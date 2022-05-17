<?php

namespace Doofinder\Feed\Block\Adminhtml\Form\Field\CustomAttributes;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Backend system config field renderer
 */
class Enable extends AbstractBlock
{
    protected function _toHtml()
    {
        $id = $this->getInputId();
        $iName = $this->getInputName();
        $cName = $this->getColumnName();
        $column = $this->getColumn();

        $size = isset($column['size']) ? $column['size'] : '';
        $class = isset($column['class']) ? $column['class'] : 'input-text';
        $style = isset($column['style']) ? $column['style'] : '';

        return "<input type=\"checkbox\" id=\"$id\" name=\"$iName\" <%- $cName %> size=\"$size\" class=\"$class\" style=\"$style\" />";
    }
}
