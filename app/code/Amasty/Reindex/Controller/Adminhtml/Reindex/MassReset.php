<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Controller\Adminhtml\Reindex;

use Magento\Backend\App\Action;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\Indexer\StateFactory;

class MassReset extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magento_Indexer::index';

    /**
     * @var \Magento\Indexer\Model\Indexer\Collection
     */
    private $indexerCollection;

    /**
     * @var StateFactory
     */
    private $stateFactory;

    public function __construct(
        CollectionFactory $indexerCollectionFactory,
        StateFactory $stateFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->indexerCollection = $indexerCollectionFactory->create();
        $this->stateFactory = $stateFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($indexerIds = $this->getRequest()->getParam('indexer_ids')) {
            $indexers = $this->indexerCollection->getAllIds();
            foreach ($indexerIds as $key => $indexerId) {
                if (in_array($indexerId, $indexers)) {
                    try {
                        $this->stateFactory->create()->loadByIndexer($indexerId)
                            ->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID)
                            ->save();
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('Couldn\'t reset indexer %1', $indexerId));
                    }
                }
            }
        }
        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
