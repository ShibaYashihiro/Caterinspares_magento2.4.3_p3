<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\UrlInterface;

class UniversalDropFiles extends \Magento\Framework\View\Element\Template
{
    /**
     * Url path  to edit
     * 
     * @var string
     */
    const URL_PATH_CSV_UPLOAD = 'mageants_partfinder/partfinders/csvUnivarsalImport';
    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
     /**
     * constructor
     * 
     * @param  Context $context,
     * @param  array $data
     */
    public function __construct(
		Context $context,
		UrlInterface $urlBuilder,
		array $data = []
	) 
	{	
	
		parent::__construct($context, $data);	
				
		$this->finder_id = $data['filter_id'];
		
		$this->_formKey = $context->getFormKey();;
		
		 $this->_urlBuilder = $urlBuilder;
		
		$this->setTemplate("Mageants_PartFinder::ImportFiles/dropUniversalFiles.phtml");
    }
     /**
     * getCsvImportUr
     * 
     */
    public function getCsvImportUrl()	 
	{	
		return $this->_urlBuilder->getUrl(
                                static::URL_PATH_CSV_UPLOAD,
                                [
                                    'finderid' => $this->getFinderId()
                                ]
                            );
    }
	/**
     * getFinderId
     * 
	 * return string form key
     */	
	public function getFinderId()
	{
		return $this->finder_id;
	}
	/**
     * getFormKey
     * 
	 * return string form key
     */	
	public function getFormKey()
	{
		return $this->_formKey->getFormKey();
	}
}
