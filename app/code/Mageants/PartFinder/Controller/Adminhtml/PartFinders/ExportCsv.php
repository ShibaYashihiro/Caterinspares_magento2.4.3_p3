<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\PartFindersFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\LayoutFactory;
		
class ExportCsv extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_save';
    /**
     * constructor
     * 
     * @param PartFindersFactory $partfindersFactory,
     * @param Registry $coreRegistry
     * @param Context $context
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
		PartFindersFactory $partfindersFactory,
		Registry $coreRegistry,
        Context $context,
		LayoutFactory $resultLayoutFactory
    )
    {
		$this->_resultLayoutFactory = $resultLayoutFactory;
		
        parent::__construct($partfindersFactory,$coreRegistry, $context );
    }
	
    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {		
		$partfinder = $this->_initPartFinder();
		
		$fileName = str_replace(" ","-",strtolower($partfinder->getName())). '-partfinder-products.csv';
		
		$resultLayout = $this->_resultLayoutFactory->create();
		
		$content = $resultLayout->getLayout()->getBlock('partfinder.partfinders.edit.tab.products')->getCsv();
		
		$this->_sendUploadResponse($fileName, $content);
    }
	
	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {

		 $this->_response->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader('Content-type', $contentType, true)
			->setHeader('Content-Length', strlen($content), true)
			->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
			->setHeader('Last-Modified', date('r'), true)
			->setBody($content)
			->sendResponse();
		die;

	 }
}
