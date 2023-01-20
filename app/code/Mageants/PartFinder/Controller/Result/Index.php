<?php
/**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Result;

use \Magento\Catalog\Model\Layer\Resolver;
use \Magento\Catalog\Model\Session;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Search\Model\QueryFactory;

class Index extends \Magento\CatalogSearch\Controller\Result\Index
{
    /**
    * @var QueryFactory
    */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;
    /**
    * @param Context $context
    * @param Session $catalogSession
    * @param StoreManagerInterface $storeManager
    * @param QueryFactory $queryFactory
    * @param Resolver $layerResolver
    */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver
    ) {
        parent::__construct($context, $catalogSession, $storeManager, $queryFactory, $layerResolver);
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
    }
    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();
        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->isMinQueryLength()) {
            $query->setId(0)->setIsActive(1)->setIsProcessed(1);
        } else {
            $query->saveIncrementalPopularity();

            $redirect = $query->getRedirect();
            if ($redirect && $this->_url->getCurrentUrl() !== $redirect) {
                $this->getResponse()->setRedirect($redirect);
                return;
            }
        }

        $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
