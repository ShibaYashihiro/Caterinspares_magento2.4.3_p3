<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Controller\Adminhtml\Reindex;

class Index extends \Amasty\Reindex\Controller\Adminhtml\AbstractReindex
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->run();
        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
