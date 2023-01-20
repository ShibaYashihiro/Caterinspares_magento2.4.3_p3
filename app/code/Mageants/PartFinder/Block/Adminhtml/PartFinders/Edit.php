<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders;

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
        
		$this->buttonList->update('save', 'label', __('Save Part Finder'));
        
		$this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
    }
	
    /**
     * Retrieve text for header element depending on loaded Part Finder
     *
     * @return string
     */
    public function getHeaderText()
    {
        $partfinderd = $this->_coreRegistry->registry('mageants_partfinders');
        
		if ($partfinderd->getId()) 
		{
            return __("Edit Part Finder '%1'", $this->escapeHtml($partfinderd->getName()));
        }
		
        return __('New Part Finders');
    }
}
