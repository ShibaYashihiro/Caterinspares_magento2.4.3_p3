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
use \Mageants\PartFinder\Model\ResourceModel\PartFinderUniversalProducts\CollectionFactory as PartFinderUniversalProductsFactory;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class UniversalProducts extends \Magento\Backend\Block\Widget\Grid\Extended
{
	const FORM_NAME = 'mageants_partfinder_parfinders_form';
	 /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
	/**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderUniversalProducts\CollectionFactory
     */
    protected $_partFinderUniversalProductsFactory;
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
		ProductCollectionFactory $productCollectionFactory,
        PartFinderUniversalProductsFactory $partFinderUniversalProductsFactory,
		array $data = []
    )
    {	
		$this->_partFinderUniversalProductsFactory = $partFinderUniversalProductsFactory;
		
		$this->productCollectionFactory = $productCollectionFactory;
		
		$this->_formKey = $context->getFormKey();

        parent::__construct($context, $backendHelper, $data);
		
	/* 	echo "<pre>";
		print_r(get_class_methods($this));
		echo "</pre>"; */
    }
	
    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('universalProductsGrid');
		
		$this->setDefaultSort('import_start_at');
		
		$this->setDefaultDir('DESC');			

		$this->setSaveParametersInSession(true);
		
        $this->setUseAjax(true);       
    }	
	 /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv()
    {	
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->getColumns() as $column) {
				
				if($column->getData("id") !="sku") continue;
				
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        $column->getRowFieldExport($item)
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        return $csv;
    }	
	  /**
     * prepare _prepareMassaction
     */
	protected function _prepareMassaction()
	{
		$finder_id = $this->getRequest()->getParam("id");
		
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('procuct_ids');
		
		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('*/*/massDeleteUniversalProduct/id/'.$finder_id."/form_key/".$this->_formKey->getFormKey()),
				'confirm' => __("Are you sure you wan't to delete selected product from current part finder?")
			]
		);

		return $this;
	}
    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
		$finder_id = $this->getRequest()->getParam("id");
		
		$export_url = '*/*/exportUniversalProductCsv/id/'.$finder_id;
		
		$this->addExportType($export_url,__('CSV'));        
		
		$universalProductsCollection = $this->_partFinderUniversalProductsFactory->create();
		
		$universal_products_table = $universalProductsCollection->getMainTable();
		
		$collection = $this->productCollectionFactory->create();
		
		$collection->addAttributeToSelect(['name','sku']);  
		
		$collection  
			 ->getSelect()
			 ->joinLeft(
				   ['universal_products_table' => $universal_products_table],
				   "universal_products_table.product_id = e.entity_id ",
				   []
			   )->where('e.entity_id IN (universal_products_table.product_id) AND universal_products_table.finder_id = '.$finder_id);;
			   
        $this->setCollection($collection);
		
		return parent::_prepareCollection();
    }
    /**
     * @return $this
     */
    protected function _prepareColumns()
    {	
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
				'type'=>'number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        return parent::_prepareColumns();
    }
	
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/universalProducts', ['_current' => true,'form_key'=>$this->_formKey->getFormKey()]);
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
