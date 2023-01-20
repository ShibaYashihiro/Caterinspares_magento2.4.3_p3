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
use \Magento\Config\Model\Config\Source\Yesno;

class Imports extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	const FORM_NAME = 'mageants_partfinder_parfinders_form';
   /**
     * Yes No options
     */
    protected $_yesNo;
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
		Yesno $yesNo,
        array $data = []
    )
    {	
		$this->_yesNo  = $yesNo;
		
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
		
        $form->setHtmlIdPrefix('imports_');
        $form->setFieldNameSuffix('imports');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Imports'),
                'class'  => 'fieldset-wide'
            ]
        );
		
		$fieldset->addField(
            'import_delete_existing',
            'select',
            [
				'label' => __('Delete Existing Data'),
                'title' => __('Delete Existing Data'),
                'name' => 'import_delete_existing',
				'values' => $this->_yesNo->toOptionArray()
            ]
        );
		
	   $formData = $partfinders->getData();
		
       $form = $this->addProductFieldset($form,$formData);
		
        $this->setForm($form);
		
        return parent::_prepareForm();
    }
	  /**
     * Add Product fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $formData
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addProductFieldset($form, $formData)
    {
		$partfinders = $this->_coreRegistry->registry('mageants_partfinders');
		
        $importFilesGridBlock = $this->getLayout()->createBlock(
            ImportFilesGrid::class,
            null,
            ['data' => ['finder_id' =>$partfinders->getId()]]
        );

        $dropFilesBlock = $this->getLayout()->createBlock(
            DropFiles::class,
            null,
            ['data' => ['finder_id' =>$partfinders->getId()]]
        );

        $importFilesFieldset = $form->addFieldset('import_files_fieldset', []);
     	 
		
        $importFilesFieldset->addField(
            'drop_file_container',
            'note',
            [
                'label' => __('Drop File Here'),
                'title' => __('Drop File Here'),
                'text' => $dropFilesBlock->toHtml()
            ]
        );
        $importFilesFieldset->addField(
            'import_files_grid_container',
            'note',
            [
			
                'label' => __('Imported Files'),
                'title' => __('Imported Files'),
                'text' => $importFilesGridBlock->toHtml()
            ]
        );
	  
        return $form;
    }
	
    /**
     * Prepare PartFinders for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Imports');
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
