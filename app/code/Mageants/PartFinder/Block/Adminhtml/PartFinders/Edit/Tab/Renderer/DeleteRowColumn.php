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
 * @package  Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab\Renderer
 */
class DeleteRowColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
   /**
     * Url path  to delete
     * 
     * @var string
     */
    const URL_PATH_DELETE = 'mageants_partfinder/partfinders/deleteRow';

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
		$deleteUrl = $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'val_map_id' => $row->getId()
                                ]
                            );
				
		$delete = "<a href='{$deleteUrl}' class='delete-product-row' title='". __('Delete') . "' data-confirm-text='". __('Are you sure to delete') . " ".$row->getName()."'>". __('Delete') . "</a>";
		
		return $delete;
    }
}
