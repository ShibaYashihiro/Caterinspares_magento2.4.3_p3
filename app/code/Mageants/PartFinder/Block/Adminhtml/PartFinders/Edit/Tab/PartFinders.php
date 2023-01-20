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
use \Mageants\PartFinder\Model\Source\FinderTemplates;
use \Magento\Cms\Ui\Component\Listing\Column\Cms\Options;

class PartFinders extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	const FORM_NAME = 'mageants_partfinder_parfinders_form';
    /**
     * Enable / Disable options
     */
    protected $_status;  
	/**
     * Horizontal / Verticle / Responsive / Default options
     */
    protected $_findertemplates;
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
		FinderTemplates $findertemplates,
		Options $cmsOpt,
        array $data = []
    )
    {	
        $this->_cmsOpt 				 = $cmsOpt;
		
		$this->_status 					 = $status;
		
		$this->_findertemplates 					 = $findertemplates;
		
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
		
        $form = $this->_formFactory->create();
		
        $form->setHtmlIdPrefix('partfinder_');
        $form->setFieldNameSuffix('partfinder');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Setting'),
                'class'  => 'fieldset-wide'
            ]
        );
		
      if ($partfinders->getId()) 
	  {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
		
        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
				'values' => $this->_status->toOptionArray()
            ]
        );
		$fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );
		
      if (!$partfinders->getId()) 
	  {
            $fieldset->addField(
				'filter_option_count',
				'text',
				[
					'name'  => 'filter_option_count',
					'label' => __('Number of Options'),
					'title' => __('Number of Options'),
					'required' => true,
				]
			);
        }
		
        $fieldset->addField(
            'finder_template',
            'select',
            [
                'name'  => 'finder_template',
                'label' => __('Template'),
                'title' => __('Template'),
                'required' => true,
				'values' => $this->_findertemplates->toOptionArray()
            ]
        );
        
		$fieldset->addField(
            'search_result_url',
            'text',
            [
                'name'  => 'search_result_url',
                'label' => __('Search Result Url'),
                'title' => __('Search Result Url'),
				'note' => 'E.g. special-category.html In most cases you don`t need to set it. Useful when you have 2 or more finders and want to show search results at specific categories. It`s NOT the url key. You can modify /partfinder/ url key in app/code/Mageants/PartFinder/etc/config.xml'
            ]
        );
		if($partfinders->getId())
		{
			$fieldset->addField(
				'short_code',
				'note',
				[
					'label' => __('Short Code'),
					'title' => __('Short Code'),
					'text' => ' <b>{{block class="Mageants\PartFinder\Block\Frontend\Form" block_id="parfinder_form_'.$partfinders->getId().'" id="'.$partfinders->getId().'"}}</b>'
				]
			);
	
			$fieldset->addField(
				'layout_reference_code',
				'note',
				[
					'label' => __('Layout Reference Code'),
					'title' => __('Layout Reference Code'),
					'text' => "<b>".htmlspecialchars('<referenceContainer name="content"> <block class="Mageants\PartFinder\Block\Frontend\Form" name="parfinder_form_'.$partfinders->getId().'"> <arguments> <argument name="id" xsi:type="string">'.$partfinders->getId().'</argument></arguments> </block> </referenceContainer>')."</b>"
				]
			);
		}
		$form->addValues($partfinders->getData()); 
		
        $this->setForm($form);
		
        return parent::_prepareForm();
    }
    /**
     * @return string
     */
    public function _afterToHtml($html)
    {
		$script = "<script>window.import_rand = ".rand().";require(['partfinder'],function($){});</script>";
		
        return parent::_afterToHtml($html).$script;
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
