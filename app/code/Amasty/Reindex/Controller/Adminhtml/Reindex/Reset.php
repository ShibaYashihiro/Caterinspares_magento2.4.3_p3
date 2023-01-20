<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Controller\Adminhtml\Reindex;

class Reset extends MassReset
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($indexerId = $this->getRequest()->getParam('indexer_id')) {
            $this->getRequest()->setParams(['indexer_ids' => [$indexerId]]);

            return parent::execute();
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
