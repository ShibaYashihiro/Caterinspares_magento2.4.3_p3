<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\Tab\Renderer;

use \Magento\Framework\UrlInterface;
use Magento\Backend\Block\Context;

/**
 * Class Thumbnail
 * @package  Mageants\PartFinder\Block\Adminhtml\PartFinders\Edit\Tab\Renderer
 */
class ActionColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
	
    /**
     * Url path  to edit
     * 
     * @var string
     */
    const URL_PATH_EDIT = 'mageants_partfinder/partfinders/startimport';

    /**
     * Url path  to delete
     * 
     * @var string
     */
    const URL_PATH_DELETE = 'mageants_partfinder/partfinders/filedelete';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
	
    /**
     * @param Context $context
     * @param ImageFileUploader $imageFileUploader
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
		
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		$startimportUrl = $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $row->getId(),
									'finder_id' => $row->getFinderId(),
									'start_from_begining' => true
                                ]
                            );
		
		$continueimportUrl = $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $row->getId(),
									'finder_id' => $row->getFinderId(),
									'start_from_begining' => false
                                ]
                            );
		
		$filedeleteUrl = $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $row->getId(),
                                    'finder_id' => $row->getFinderId()
                                ]
                            );
		
		$startimport = "<a href='{$startimportUrl}' title='".__('Start Import')."' class='partfinder_file_import'>".__('Start Import From Begining')."</a>";
		
		if($row->getProcessedRows() !=0 && $row->getProcessedRows() < $row->getTotalRow()  && $row->getProcessedRows() != $row->getTotalRow() )
		{
			$startimport .= " <span>|</span> <a href='{$continueimportUrl}' title='".__('Continue Importing')."' class='partfinder_file_import'>".__('Start Import From Last Stop')."</a> ";
		}
		
		$filedelete = "<a href='{$filedeleteUrl}' title='".__('Delete')."' class='partfinder_file_delete' data-confirm-text='".__('Are you sure to delete')." ".$row->getTitle()."'>".__('Delete')."</a>";
		
		return $startimport . " <span>|</span> " . $filedelete;
    }
}
