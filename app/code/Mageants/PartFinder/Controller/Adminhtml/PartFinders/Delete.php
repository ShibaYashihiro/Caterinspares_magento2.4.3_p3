<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

class Delete extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_delete';
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
		
        $id = $this->getRequest()->getParam('id');
		
        if ($id) 
		{
            $name = "";
			
            try 
			{
                /** @var \Mageants\PartFinder\Model\PartFinders $partfinder */
                $partfinder = $this->_partfindersFactory->create();
				
                $partfinder->load($id);
				
                $name = $partfinder->getName();
				
                $partfinder->delete();
				
                $this->messageManager->addSuccess(__('The Part Finder has been deleted.'));
				
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_partfinder_partfinder_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
				
                $resultRedirect->setPath('mageants_partfinder/*/');
				
                return $resultRedirect;
				
            } 
			catch (\Exception $e) 
			{
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_partfinder_label_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
				
                // display error message
                $this->messageManager->addError($e->getMessage());
				
                // go back to edit form
                $resultRedirect->setPath('mageants_partfinder/*/edit', ['id' => $id]);
				
                return $resultRedirect;
            }
        }
		
        // display error message
        $this->messageManager->addError(__('Part Finder to delete was not found.'));
		
        // go to grid
        $resultRedirect->setPath('mageants_partfinder/*/');
		
        return $resultRedirect;
    }
	
	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
}
