<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Mageants\PartFinder\Model\Source\StatusExtention;
use \Mageants\PartFinder\Model\Source\Orientation;
use \Magento\Cms\Ui\Component\Listing\Column\Cms\Options;
use \Mageants\PartFinder\Model\PartFinderOptionsFactory;
use Magento\Config\Model\Config\Source\Yesno;

class FinderOptions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	const FORM_NAME = 'mageants_partfinder_parfinders_form';
    /**
     * Enable / Disable options
     */
    protected $_status;   
	/**
     * Yes / No options
     */
    protected $_yesno;
	/**
     * \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory
     */
    protected $_partFinderOptionFactory ;
    /**
     * Horizintal / Virticle options
     */
    protected $_orientation;
	 /**
     * Store View options
     */
    protected $_cmsOpt;
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
		StatusExtention $status,
		Yesno $yesno,
		Options $cmsOpt,
		PartFinderOptionsFactory $partFinderOptionFactory,
        array $data = []
    )
    {	
        $this->_cmsOpt 				 = $cmsOpt;
		
		$this->_status 					 = $status;
		
		$this->_yesno 					 = $yesno;
		
		$this->_partFinderOptionFactory = $partFinderOptionFactory;
		
		parent::__construct($context, $registry, $formFactory, $data);
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
        
		$finderOptionsFactory = $this->_partFinderOptionFactory->create();
		
		$finderOptions = $finderOptionsFactory->getCollection()->addFieldToFilter('finder_id', array('in' => $partfinders->getId()))->setOrder("sort_order","ASC");
					
        $form = $this->_formFactory->create();
		
        $form->setHtmlIdPrefix('finderoptions_');
        $form->setFieldNameSuffix('finderoptions');
		
		$option_count = $partfinders->getFilterOptionCount();
		
		$tmp_i = 0;
		
		foreach($finderOptions as $key => $option)
		{
			 $fieldset = $form->addFieldset(
				"base_fieldset_{$option->getId()}",
				[
					'legend' => __("Option #".(++$tmp_i)),
					'class'  => 'fieldset-wide'					
				]
			);
		
			$fieldset->addType('number', 'Mageants\PartFinder\Block\Adminhtml\PartFinders\Helper\Number');        
		
			$fieldset->addField(
				"name_{$option->getId()}",
				'text',
				[
					'name'  => "name[{$option->getId()}]",
					'label' => __('Option Name'),
					'title' => __('Option Name'),
					'required' => true,
					'value'=>$option->getName()
				]
			);
			
			$fieldset->addField(
				"sort_order_{$option->getId()}",
				'number',
				[
					'name'  => "sort_order[{$option->getId()}]",
					'label' => __('Sort Order'),
					'title' => __('Sort Order'),
					'required' => true,
					'style'=> "width:100px",
					'value'=>$option->getSortOrder()
				]
			);
			/* $fieldset->addField(
				"is_range_{$option->getId()}",
				'select',
				[
					'name'  => "is_range[{$option->getId()}]",
					'label' => __('Range'),
					'title' => __('Range'),
					'required' => true,
					'value'=>$option->getIsRange(),
					'values' => $this->_yesno->toOptionArray()
				]
			); */
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
        return __('Options');
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
