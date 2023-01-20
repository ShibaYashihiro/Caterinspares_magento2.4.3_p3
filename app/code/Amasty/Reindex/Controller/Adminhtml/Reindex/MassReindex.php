<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Controller\Adminhtml\Reindex;

use Magento\Backend\App\Action;

class MassReindex extends \Amasty\Reindex\Controller\Adminhtml\AbstractReindex
{
    /**
     * @var \Magento\Indexer\Model\Indexer\Collection
     */
    private $indexerCollection;

    public function __construct(
        \Symfony\Component\Process\PhpExecutableFinder $phpExecutableFinder,
        \Magento\Framework\Shell $shell,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        Action\Context $context
    ) {
        $this->indexerCollection = $indexerCollectionFactory->create();
        parent::__construct($phpExecutableFinder, $shell, $context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($indexerIds = $this->getRequest()->getParam('indexer_ids')) {
            $indexers = $this->indexerCollection->getAllIds();
            foreach ($indexerIds as $key => $indexerId) {
                if (!in_array($indexerId, $indexers)) {
                    unset($indexerIds[$key]);
                    $this->messageManager->addWarningMessage(__('Unknown indexer code:' . $indexerId));
                }
            }
            if (!empty($indexerIds)) {
                $this->run($indexerIds);
            }
        }
        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
