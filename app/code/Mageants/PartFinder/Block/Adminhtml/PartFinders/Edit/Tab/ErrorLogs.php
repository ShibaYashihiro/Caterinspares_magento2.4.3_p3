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
use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFilesLogs\CollectionFactory as PartFinderImportFilesLogsCollectionFactory;	

class ErrorLogs extends \Magento\Backend\Block\Widget\Grid\Extended
{
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
		PartFinderImportFilesLogsCollectionFactory $partFinderImportFilesLogsCollectionFactory,
		array $data = []
    )
    {	
		$this->_formKey = $context->getFormKey();
		
		$this->_partFinderImportFilesLogsCollectionFactory = $partFinderImportFilesLogsCollectionFactory;
		
        parent::__construct($context, $backendHelper, $data);
    }
   
    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('errorLogsGrid');
		
		$this->setDefaultSort('create_at');
		
		$this->setDefaultDir('DESC');			
		
        $this->setSaveParametersInSession(true);
		
        $this->setUseAjax(true);        
    }
	
    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
		$history_id = $this->getRequest()->getParam("history_id");
		
		$collection = $this->_partFinderImportFilesLogsCollectionFactory->create();
		
		$collection->addFieldToFilter("finder_import_history_id",$history_id);
		
        $this->setCollection($collection);
		
		return parent::_prepareCollection();
    }
	 /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'row_number',
            [
                'header' => __('Row Number'),
                'index' => 'row_number',
				'type' => 'number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        ); 
		
        $this->addColumn(
            'message',
            [
                'header' => __('Message'),
                'index' => 'message',
                'class' => 'xxx'
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
				
        return parent::_prepareColumns();
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
		$history_id = $this->getRequest()->getParam("history_id");
		
        return $this->getUrl('*/*/ErrorLog', ['_current' => true,'history_id'=>$history_id,'form_key'=>$this->_formKey->getFormKey()]);
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
