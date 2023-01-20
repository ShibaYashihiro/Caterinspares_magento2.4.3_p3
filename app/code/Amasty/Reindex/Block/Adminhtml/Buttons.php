<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Block\Adminhtml;

class Buttons extends \Magento\Backend\Block\Widget\Container
{
    public function _construct()
    {
        $this->buttonList->add(
            'amreindex',
            [
                'class' => 'primary',
                'label' => __('Reindex All'),
                'on_click' => 'location.href="' . $this->_urlBuilder->getUrl('amreindex/reindex') . '";'
            ],
            10
        );

        parent::_construct();
    }
}
