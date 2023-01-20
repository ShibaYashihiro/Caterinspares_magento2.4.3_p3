<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Block\Backend\Grid\Column\Renderer;

class Reset extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render indexer reset
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<button onclick="location.href=\''
            . $this->getUrl('amreindex/reindex/reset', ['indexer_id' => $this->_getValue($row)]) . '\'"'
            . ' style="width:100%;padding:0">'
            . $this->escapeHtml(__('Reset')) . '</button>';
    }
}
