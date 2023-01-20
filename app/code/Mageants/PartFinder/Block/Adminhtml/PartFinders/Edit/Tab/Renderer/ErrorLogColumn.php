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
class ErrorLogColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
   /**
     * Url path  to delete
     * 
     * @var string
     */
    const URL_PATH_ERROR_LOG = 'mageants_partfinder/partfinders/ErrorLog';

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
		$html = $row->getCountErrors();
		
		if($row->getCountErrors())
		{
			$logUrl = $this->_urlBuilder->getUrl(
									static::URL_PATH_ERROR_LOG,
									[
										'history_id' => $row->getId()
									]
								);
					
			$html .= " <a href='{$logUrl}' class='view-log' title='".__("View Error Log")."' >".__("View")."</a>";
		}
		return $html;
    }
}
