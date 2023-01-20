<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml;

use \Mageants\PartFinder\Model\PartFindersFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
		
abstract class PartFinders extends \Magento\Backend\App\Action
{
    /**
     * Post Factory
     * 
     * @var \Mageants\PartFinder\Model\PartFindersFactory
     */
    protected $_partfindersFactory;
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Result redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * constructor
     * 
     * @param PartFindersFactory $partfindersFactory
     * @param Registry $coreRegistry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        PartFindersFactory $partfindersFactory,
        Registry $coreRegistry,
        Context $context
    )
    {
        $this->_partfindersFactory           = $partfindersFactory;
		
        $this->_coreRegistry          = $coreRegistry;
		
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
		
        parent::__construct($context);
    }

    /**
     * Init Part Finder
     *
     * @return \Mageants\PartFinder\Model\PartFinder
     */
    protected function _initPartFinder()
    {
        $partfinderid  = (int) $this->getRequest()->getParam('id');
		
        /** @var \Mageants\PartFinder\Model\PartFinders $partfinder */
        $partfinder    = $this->_partfindersFactory->create();
		
		if(!$partfinderid )
	   {
		   $partfinder_data  =  $this->getRequest()->getParam('partfinder');
		  
			if(isset($partfinder_data['id']))
				$partfinderid = (int)$partfinder_data['id'];
	   }
	   
        if ($partfinderid) 
		{
            $partfinder->load($partfinderid);
        }
		
        $this->_coreRegistry->register('mageants_partfinders', $partfinder);
		
        return $partfinder;
    }
}
