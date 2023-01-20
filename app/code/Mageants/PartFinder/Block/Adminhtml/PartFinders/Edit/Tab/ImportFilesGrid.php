<?php
/**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\Tab;

use \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFiles\CollectionFactory as ImportFilesCollectionFactory;

class ImportFilesGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinderImportFiles\CollectionFactory
     */
    protected $importFilesCollectionFactory;
        /**
     * @var  array
     */
    protected $finder_id = 0;
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param ContactFactory $attachmentFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ImportFilesCollectionFactory $importFilesCollectionFactory,
        array $data = []
    ) 
	{		
        parent::__construct($context, $backendHelper, $data);
		
		$this->_formKey = $context->getFormKey();
		
		$this->finder_id = $this->getRequest()->getParam("id");
		
		if(isset($data['finder_id']))
		{
			$this->finder_id = $data['finder_id'];
		}
		
        $this->importFilesCollectionFactory = $importFilesCollectionFactory;
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('importFilesGrid');
		
        $this->setDefaultSort('create_at');
		
        $this->setDefaultDir('DESC');
		
        $this->setSaveParametersInSession(true);
		
        $this->setUseAjax(true);        
    }
	/**
     * prepare _prepareMassaction
     */
	protected function _prepareMassaction()
	{
		$finder_id = $this->getRequest()->getParam("id");
		
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('file_ids');
		
		$this->getMassactionBlock()->addItem(
			'delete',
			[
				'label' => __('Delete'),
				'url' => $this->getUrl('*/*/massDeleteImportFiles/id/'.$finder_id."/form_key/".$this->_formKey->getFormKey()),
				'confirm' => __("Are you sure you want to delete selected file? All data related to this file is deleted.")
			]
		);

		return $this;
	}
    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->importFilesCollectionFactory->create();
		
        //$collection->addAttributeToSelect('*');
		
		$collection->addFieldToFilter('finder_id', array('in' => $this->finder_id));
		
        $this->setCollection($collection);
		
		return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('File ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
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
            'processed_rows',
            [
                'header' => __('Process Rows'),
                'index' => 'processed_rows',
				'type' => 'number',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'total_row',
            [
                'header' => __('Total Record'),
                'index' => 'total_row',
                'class' => 'xxx',
                'width' => '50px',
				'type'   => 'number'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Upload Date'),
                'index' => 'created_at',
                'width' => '50px',
				'type'   => 'datetime'
            ]
        );
		
		$this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'action',
				'renderer' => Renderer\ActionColumn::class,
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
    public function _afterToHtml($html)
    {
		$script = "<script>window.import_rand=".rand().";</script>";
		
        return parent::_afterToHtml($html).$script;
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
		$finder_id = $this->getRequest()->getParam("id");
		
        return $this->getUrl('*/*/importFilesGrid', ['_current' => true,'id'=>$finder_id,'form_key'=>$this->_formKey->getFormKey()]);
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