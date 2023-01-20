<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\AddNewRow;

use \Magento\Framework\Registry;
use \Magento\Backend\Block\Widget\Context;
		
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    )
    {
        $this->_coreRegistry = $coreRegistry;
		
        parent::__construct($context, $data);		
    }
	
    /**
     * Getter of url for "Save" button
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/saveNewProduct', ['_current' => true]);
    }
    /**
     * Initialize Part Finder edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        
		$this->_blockGroup = 'Mageants_PartFinder';
        
		$this->_controller = 'adminhtml_partFinders';
		
        parent::_construct();
		
		$finder_id = $this->_coreRegistry->registry('mageants_partfinders')->getId();
		
		$back_url = $this->getUrl('*/*/edit/id/'.$finder_id, ['_current' => true]);
		$this->buttonList->update('back', 'onclick', "setLocation('{$back_url}')");

		$this->buttonList->remove('reset');        
		$this->buttonList->remove('delete');		
    }	
}
