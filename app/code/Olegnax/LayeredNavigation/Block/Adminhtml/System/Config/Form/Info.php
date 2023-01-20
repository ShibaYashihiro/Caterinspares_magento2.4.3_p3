<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\LayeredNavigation\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Olegnax\Core\Block\Adminhtml\NoticeContent;
use \Magento\Backend\Block\Template\Context;

/** @noinspection ClassNameCollisionInspection */

class Info extends Field
{
    protected $content;

    public function __construct(
        Context $context,
        NoticeContent $content,
        array $data = []
    ) {
        $this->content = $content;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<div style="background-color: #ffffff;border: 1px solid #e6ebf0;padding: 1.5rem;margin-bottom:5px;box-shadow: 0px 3px 13px 0px rgba(216,225,234,.48);">
            <p style="margin:0">Layered Navigation Extension v1.0.5 - Made by <a href="https://olegnax.com" target="_blank">Olegnax</a></p>
			</div>';
        $html .= $this->getContent();
        return $html;
    }

    protected function getContent()
    {
        $this->content->setData('location', $this->getModuleName());
        return $this->content->toHtml();
    }
}

