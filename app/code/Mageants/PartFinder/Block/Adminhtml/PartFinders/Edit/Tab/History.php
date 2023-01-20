<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data;
use \Magento\Framework\Registry;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory as PartFinderImportFilesHistoryCollectionFactory;

class History extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
     * PartFindersFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFindersFactory
     */
    protected $_partFinders;  
	/**
     *  PartFinderImportFilesHistoryCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesHistory\CollectionFactory
     */
    protected $_partFinderImportFilesHistoryCollectionFactory;
    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;
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
        Data $backendHelper,
        Registry $registry,
        PartFinderImportFilesHistoryCollectionFactory $partFinderImportFilesHistoryCollectionFactory,
		array $data = []
    )
    {	
		$this->_formKey = $context->getFormKey();
	
        $this->registry = $registry;
		
		$this->_partFinders = $this->registry->registry('mageants_partfinders');
		
		$this->_partFinderImportFilesHistoryCollectionFactory = $partFinderImportFilesHistoryCollectionFactory;
		
        parent::__construct($context, $backendHelper, $data);
    }
   
    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('historyGrid');
		
		$this->setDefaultSort('import_start_at');
		
		$this->setDefaultDir('DESC');			

		$this->setSaveParametersInSession(true);
		
        $this->setUseAjax(true);        
    }

    /**
     * add Column Filter To Collection
     */
    protected function _addColumnFilterToCollection($column)
    {
        if($column->getId() == "name")
		{
			$this->getCollection()->getSelect()->where("LOWER(catalog_product_entity_varchar_table.value) like '%".strtolower($column->getFilter()->getValue()) ."%' ");
		}
		else 
		{
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
	  /**
     * prepare _prepareMassaction
     */
	protected function _prepareMassaction()
	{
		$finder_id = $this->getRequest()->getParam("id");
		
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('history_ids');
		
		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('*/*/massDeleteHistory/id/'.$finder_id."/form_key/".$this->_formKey->getFormKey()),
				'confirm' => __("Are you sure you want to delete selected history? All data related to this history is deleted.")
			]
		);

		return $this;
	}
    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
		$finder_id = $this->_partFinders->getId();
		
		$collection = $this->_partFinderImportFilesHistoryCollectionFactory->create();
		
		$collection->addFieldToFilter("finder_id",$finder_id);
		
        $this->setCollection($collection);
		
		return parent::_prepareCollection();
    }
    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
		$finder_id = $this->_partFinders->getId();
		
        $this->addColumn(
            'file_name',
            [
                'header' => __('File Name'),
                'index' => 'file_name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        $this->addColumn(
            'import_start_at',
            [
                'header' => __('Started'),
                'index' => 'import_start_at',
				'type'=>'datetime',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        $this->addColumn(
            'import_ended_at',
            [
                'header' => __('Finished'),
                'index' => 'import_ended_at',
				'type'=>'datetime',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        $this->addColumn(
            'total_row',
            [
                'header' => __('Total Row'),
                'index' => 'total_row',
				'type'=>'number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
		
        $this->addColumn(
            'processed_rows',
            [
                'header' => __('Processed Rows'),
                'index' => 'processed_rows',
				'type'=>'number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
		
        $this->addColumn(
            'count_errors',
            [
                'header' => __('Error'),
                'index' => 'count_errors',
				'renderer' => Renderer\ErrorLogColumn::class,
				'type'=>'number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
		/* echo "<pre>";
		print_r(get_class_methods($this));exit; */
        return parent::_prepareColumns();
    }
	
    /**
     * @return string
     */
     public function _afterToHtml($html)
    {
		$script = "
		<div id='historyLogModel'><div id='historyloggrid' ></div></div>
		<script>
		window.view_log_rand = ".rand().";
		var log_loding = jQuery(\"<div class='loading-mask' data-role='loader'><div class='popup popup-loading'><div class='popup-inner'><img alt='Loading...' src='".$this->getViewFileUrl('Magento_backend::images/loader-2.gif')."'>Please wait...</div></div></div>\");
	require(['partfinderhistorylog'],function($){});
	</script>";
        return parent::_afterToHtml($html).$script;
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/history', ['_current' => true,'form_key'=>$this->_formKey->getFormKey()]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
