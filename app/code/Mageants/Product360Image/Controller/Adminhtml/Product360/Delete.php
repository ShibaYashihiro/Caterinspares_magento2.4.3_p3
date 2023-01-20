<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Controller\Adminhtml\Product360;

class Delete extends \Mageants\Product360Image\Controller\Adminhtml\Product360
{
    /**
     * Access Resource ID
     *
     */
    const RESOURCE_ID = 'Mageants_Product360Image::product360_delete';
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $id = $this->getRequest()->getParam('id');
        
        if ($id) {
            $name = "";
            
            try {
                /** @var \Mageants\Product360Image\Model\Product360 $product360 */
                $product360 = $this->product360Factory->create();
                
                $product360->load($id);
                
                $name = $product360->getName();
                
                $product360->delete();
                
                $this->messageManager->addSuccess(__('The Products 360 has been deleted.'));
                
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_product360image_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                
                $resultRedirect->setPath('catalog/product/index');
                
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mageants_product360image_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                
                // display error message
                $this->messageManager->addError($e->getMessage());
                
                // go back to edit form
                $resultRedirect->setPath('mageants_product360image/*/edit', ['id' => $id]);
                
                return $resultRedirect;
            }
        }
        
        // display error message
        $this->messageManager->addError(__('Product 360 was not found.'));
        
        // go to grid
        $resultRedirect->setPath('mageants_product360image/*/');
        
        return $resultRedirect;
    }
     /*
	 * Check permission via ACL resource
	 */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::RESOURCE_ID);
    }
}
