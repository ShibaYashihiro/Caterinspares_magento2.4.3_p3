<?php
/**
 * @author      Olegnax
 * @package     Olegnax_InfiniteScroll
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\InfiniteScroll\Block\Adminhtml\System\Config\Form;

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
            <p>Infinite Scroll Extension v1.0.2 - Made by <a href="https://olegnax.com" target="_blank">Olegnax</a></p>
			<a href="https://olegnax.com/product/magento-2-infinite-scroll-extension/" target="_blank">Extension Details Page</a>&nbsp;|&nbsp;<a href="https://olegnax.com/documentation/magento-2-infinite-scroll-extension/" target="_blank">Documentation</a>&nbsp;|&nbsp;<a href="https://olegnax.com/documentation/magento-2-infinite-scroll-extension-changelog/" target="_blank">Changelog</a>
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

