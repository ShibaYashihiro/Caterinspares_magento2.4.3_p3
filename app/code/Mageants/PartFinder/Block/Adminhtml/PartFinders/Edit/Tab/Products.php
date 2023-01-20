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
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory as PartFinderOptionValuesCollectionFactory;	
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory as PartFinderOptionValueMapCollectionFactory;
use \Mageants\PartFinder\Model\PartFinderOptionValuesFactory as PartFinderOptionValuesFactory;	
use \Mageants\PartFinder\Model\PartFinderOptionValueMapFactory as PartFinderOptionValueMapFactory;
use \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory as PartFinderOptionsCollectionFactory;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
     * PartFindersFactory model
     * 
     * @var \Mageants\PartFinder\Model\PartFindersFactory
     */
    protected $_partFinders;  
	/**
     * CollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptions\CollectionFactory
     */
    protected $_partFinderOptionsCollection;
	/**
     *  PartFinderOptionValuesCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValues\CollectionFactory
     */
    protected $_partFinderOptionValuesCollectionFactory;
	/**
     * PartFinderOptionValueMapCollectionFactory model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderOptionValueMap\CollectionFactory
     */
    protected $_partFinderOptionValueMapCollectionFactory;
	/**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;	
    /**
     * @var  array
     */
    protected $_prod_ids;
	/**
     * @var  array
     */
    protected $_finder_options;
    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;
	/**	
     * @var  \Magento\Framework\ObjectManagerInterface 
     */
    protected $_objectManager = null;
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
        ObjectManagerInterface $objectManager,
        CollectionFactory $productCollectionFactory,
		PartFinderOptionsCollectionFactory $partFinderOptionCollectionFactory,
		PartFinderOptionValuesCollectionFactory $partFinderOptionValueCollectionFactory,
		PartFinderOptionValueMapCollectionFactory $partFinderOptionValueMapCollectionFactory,
		array $data = []
    )
    {	
		$this->_formKey = $context->getFormKey();
	
		$this->productCollectionFactory = $productCollectionFactory;
		
        $this->_objectManager = $objectManager;
		
        $this->registry = $registry;
		
		$this->_partFinders = $this->registry->registry('mageants_partfinders');
		
		$this->_partFinderOptionsCollection = $partFinderOptionCollectionFactory;
				
		$this->_partFinderOptionValuesCollectionFactory = $partFinderOptionValueCollectionFactory;
		
		$this->_partFinderOptionValueMapCollectionFactory = $partFinderOptionValueMapCollectionFactory;
		
		$partFinderOptionsCollection = $this->_partFinderOptionsCollection->create();
				
		$partFinderOptionsCollection->addFieldToFilter("finder_id",$this->_partFinders->getId());
		
		$finder_options=[];
		
		foreach($partFinderOptionsCollection as $fnd_opt)
		{
			$finder_options[$fnd_opt->getId()] = $fnd_opt->getData();
		}
		
		$this->_finder_options = $finder_options;
		
        parent::__construct($context, $backendHelper, $data);
    }
   
    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('productsGrid');
		
		$finder_options = $this->_finder_options;
		
		foreach($finder_options as $opt_id => $opt_data)
		{
			$this->setDefaultSort('value_'.$opt_id);
			
			$this->setDefaultDir('ASC');			
		}
		
        $this->setSaveParametersInSession(true);
		
        $this->setUseAjax(true);        
    }

    /**
     * add Column Filter To Collection
     */
    protected function _addColumnFilterToCollection($column)
    {
		$value_columns = [];
		
		$finder_options = $this->_finder_options;
		
		foreach($finder_options as $opt_id => $opt_data)
		{
			$value_columns["value_".$opt_id] = "value_".$opt_id.".value";
		}
		
        if (isset($value_columns[$column->getId()]) ) 
		{
			$field = $value_columns[$column->getId()];
            $this->getCollection()->getSelect()->where("LOWER(".$field .") like '%".strtolower($column->getFilter()->getValue()) ."%' ");
        } 
		else if($column->getId() == "name")
		{
			$this->getCollection()->getSelect()->where("LOWER(catalog_product_entity_varchar_table.value) like '%".strtolower($column->getFilter()->getValue()) ."%' ");
		}
		else if($column->getId() == "sku")
		{
			$this->getCollection()->getSelect()->where("main_table.sku like '%".$column->getFilter()->getValue() ."%' ");
		}
		else 
		{
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
	 /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv()
    {
		$value_columns = [];
		
		$finder_options = $this->_finder_options;
		
		foreach($finder_options as $opt_id => $opt_data)
		{
			$value_columns["value_".$opt_id] = "value_".$opt_id.".value";
		}
		
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->getColumns() as $column) {
			
			if(!isset($value_columns[$column->getData("id")]) && $column->getData("id") !="sku") continue;
			
            if (!$column->getIsSystem()) {
				$data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->getColumns() as $column) {
				
				if(!isset($value_columns[$column->getData("id")]) && $column->getData("id") !="sku") continue;
				
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

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->getColumns() as $column) {
				
				if(!isset($value_columns[$column->getData("id")]) && $column->getData("id") !="sku") continue;
				
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        $column->getRowFieldExport($this->getTotals())
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
		
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('value_map_ids');
		
		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('*/*/massDeleteImportedDataRows/id/'.$finder_id."/form_key/".$this->_formKey->getFormKey()),
				'confirm' => __("Are you sure you want to delete?")
			]
		);

		return $this;
	}
	
    /**
     * @return string
     */
    public function _afterToHtml($html)
    {
		$script = "<script>window.product_grid_rand=".rand()."; require(['partfinder-products'],function($){});</script>";
		
        return parent::_afterToHtml($html).$script;
    }
    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
		$prod_collection = $this->productCollectionFactory->create();
		
		$product_table = $prod_collection->getMainTable();
		
		$prod_collection->addAttributeToSelect('name');
		
		
		
		/*Join Part Finder Datas*/
		$finder_options = $this->_finder_options;
		
		$partFinderOptionValueMapCollection = $this->_partFinderOptionValueMapCollectionFactory->create();
		
		$collection = $partFinderOptionValueMapCollection->addFieldToSelect(['id','sku','product_id']);
		
		$collection  
			 ->getSelect()
			 ->group("main_table.id")
			 ->joinLeft(
				   ['product_table' => $product_table],
				   'main_table.product_id = product_table.entity_id',
				   []
			   )
			   ->where('product_table.entity_id IN (main_table.product_id)');
		
		/*Add Name Attribute with select column*/
		$prod_entity_type_id = $prod_collection->getEntity()->getTypeId();
		
		$catalog_product_entity_varchar_table = $collection->getResource()->getTable('catalog_product_entity_varchar');
		
		$eav_attribute_table = $collection->getResource()->getTable('eav_attribute');
		
		$collection  
			 ->getSelect()
			 ->joinLeft(
				   ['eav_attribute_table' => $eav_attribute_table],
				   "eav_attribute_table.attribute_code = 'name' AND eav_attribute_table.entity_type_id =  {$prod_entity_type_id}",
				   []
			   );
		$collection  
			 ->getSelect()
			 ->joinLeft(
				   ['catalog_product_entity_varchar_table' => $catalog_product_entity_varchar_table],
				   " catalog_product_entity_varchar_table.entity_id = main_table.product_id AND catalog_product_entity_varchar_table.attribute_id = eav_attribute_table.attribute_id AND catalog_product_entity_varchar_table.store_id = 0",
				   ['name'=>'catalog_product_entity_varchar_table.value']
			   );
			   
		$partFinderOptionValueCollection = $this->_partFinderOptionValuesCollectionFactory->create();
		
		$value_table = $partFinderOptionValueCollection->getMainTable();
		
		$last_table_field = 'main_table.value_id' ;
		
		$include_opt_ids = implode(",",array_keys($finder_options));
		
		$sort_order=[];
		
		foreach(array_reverse(array_keys($finder_options)) as $opt_id)
		{
			 $collection  
			 ->getSelect()
			 ->joinLeft(
				   ['value_'.$opt_id => $value_table],
				   $last_table_field.' = value_'. $opt_id .'.id',
				   ['value'.$opt_id=>'value_'. $opt_id .'.value','id'.$opt_id=>'value_'. $opt_id .'.id']
			   )
			   ->where("value_{$opt_id}.option_id IN ({$include_opt_ids})");			
			   
			   $last_table_field = 'value_'.$opt_id.".parent_id";
			   
			   $sort_order[] = 'value_'.$opt_id.".value";			
		}	
		
		if(isset($sort_order) && !empty($sort_order))
		{
			foreach(array_reverse($sort_order) as $order)
			{
				$collection->getSelect()->order($order,'ASC');
			}
		}
        $this->setCollection($collection);
		
		return parent::_prepareCollection();
    }
    /**
     * prepare collection
     */
    protected function _prepareNewAndRemoveRowButton()
	{
		
		$finder_id = $this->_partFinders->getId();
		
		$add_new_url = $this->getUrl('*/*/addNewRow/id/'.$finder_id, ['_current' => true]);
		
		$this->setChild(
            'add_new_btn',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
			[
				'label' => __('Add New '),                    
				'onclick' => "setLocation('{$add_new_url}')",
				'class' => 'primary',
			]
            )
        );
		
		$delete_all_url = $this->getUrl('*/*/removeAllProducts/id/'.$finder_id, ['_current' => true]);
		
		$this->setChild(
            'remove_all_btn',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
			[
				'label' => __('Remove All Product'),				
				'onclick' => "deleteConfirm('Are you sure to delete all product data?','{$delete_all_url}')",
			]
            )
        );
	}
	/**
     * Generate export button
     *
     * @return string
     */
    public function getExportButtonHtml()
    {
		$this->_prepareNewAndRemoveRowButton();

        return $this->getChildHtml('export_button') . $this->getChildHtml('remove_all_btn') . $this->getChildHtml('add_new_btn')  ;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
		$finder_id = $this->_partFinders->getId();
		
		$export_url = '*/*/exportCsv/id/'.$finder_id;
		
		$this->addExportType($export_url,__('CSV'));                              
		
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'product_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
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
		
		$finder_options = $this->_finder_options;
		foreach($finder_options as $opt_id => $opt_data)
		{
			$this->addColumn(
				'value_'.$opt_id,
				[
					'header' => $opt_data['name'],
					'index' => 'value'.$opt_id,
					'sortable' => false					
				]
			);
		}
		
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
		
		$this->addColumn(
            'action',
            [
                'header' => __('Delete'),
                'index' => 'action',
				'renderer' => Renderer\DeleteRowColumn::class,
                'class' => 'xxx',
                'width' => '50px',
				'filter' => false,
				'sortable' => false
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/products', ['_current' => true,'form_key'=>$this->_formKey->getFormKey()]);
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
