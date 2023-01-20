<?php
 /**
 * @category  Mageants Part Finder 
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml;

class PartFinders extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_partfinders';
		
        $this->_blockGroup = 'Mageants_PartFinder';
		
        $this->_headerText = __('Part Finder');
		
        $this->_addButtonLabel = __('Add Part Finder');
		
        parent::_construct();
    }
}
