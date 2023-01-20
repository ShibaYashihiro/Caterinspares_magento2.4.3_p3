<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\AddNewRow\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory as PartFinderOptionsCollectionFactory;

class NewProduct extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	const FORM_NAME = 'mageants_partfinder_parfinders_new_product_form';    
	/**
     * CollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory
     */
    protected $_partFinderOptionsCollection;
	/**
     * constructor
     * 
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
	 public function __construct(
		Context $context,
        Registry $registry,
        FormFactory $formFactory,
		PartFinderOptionsCollectionFactory $partFinderOptionsCollectionFactory,
        array $data = []
    )
    {	
		$this->_partFinderOptionsCollection = $partFinderOptionsCollectionFactory;
		
		parent::__construct($context, $registry, $formFactory, $data);
    }
	/**
     * Getter of url for "Save" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/saveNewProduct', ['_current' => true]);
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Mageants\PartFinder\Model\PartFinders $partfinders */
        $partfinders = $this->_coreRegistry->registry('mageants_partfinders');
		
		
        $form = $this->_formFactory->create();
		
        $form->setHtmlIdPrefix('newproduct_');
        $form->setFieldNameSuffix('newproduct');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('New Product Data'),
                'class'  => 'fieldset-wide'
            ]
        );
		
      if ($partfinders->getId()) 
	  {
            $fieldset->addField(
                'finder_id',
                'hidden',
                [
					'name' => 'id',
					'value' => $partfinders->getId()
				]
            );
       }
		
		$fieldset->addField(
            'sku',
            'text',
            [
                'name'  => 'sku',
                'label' => __('Sku'),
                'title' => __('Sku'),
                'required' => true,
            ]
        );
		
		$partFinderOptionsCollection = $this->_partFinderOptionsCollection->create();
				
		$partFinderOptionsCollection->addFieldToFilter("finder_id",$partfinders->getId())->setOrder("sort_order","ASC");
		
		$finder_options=[];
		
		foreach($partFinderOptionsCollection as $fnd_opt)
		{
			$opt_id = $fnd_opt->getId();
			
			$opt_label = $fnd_opt->getName();
			
			$fieldset->addField(
				'option_'.$opt_id,
				'text',
				[
					'name'  => "option[{$opt_id}]",
					'label' => $opt_label,
					'title' => $opt_label,
					'required' => true,
				]
			);
		}
		
        $this->setForm($form);
		
        return parent::_prepareForm();
    }
	
    /**
     * Prepare PartFinders for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

}
