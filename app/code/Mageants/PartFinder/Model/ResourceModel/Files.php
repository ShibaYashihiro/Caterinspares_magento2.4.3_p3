<?php
 /**
 * @category  Mageants PartFinder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Model\ResourceModel;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\Filesystem;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Store\Model\StoreManagerInterface;
		
class Files
{
	
	/**
     * @var _storeManager
     */
	protected $_storeManager;
    /**
     * Media sub folder
     * 
     * @var string
     */
    protected $_subDir = 'mageants/PartFinder/PartFinders';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * File system model
     * 
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Framework\View\Asset\Repositoryp
     */
    protected $_assetRepo;

    /**
     * constructor
     * 
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem,
		Repository $assetRepo,
		StoreManagerInterface $storeManager
    )
    {
        $this->_urlBuilder = $urlBuilder;
		
        $this->_fileSystem = $fileSystem;
		
		$this->_assetRepo = $assetRepo;
		
		$this->_storeManager = $storeManager;
    }

    /**
     * get csv base url
     *
     * @return string
     */
    public function getBaseUrl($finder_id)
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->_subDir.'/csv/'.$this->getConvertedId($finder_id);
    }
    
	/**
     * get base csv dir
     *
     * @return string
     */
    public function getBaseDir($finder_id = '') 
    {
        return $this->_fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath($this->_subDir.'/csv/'.$this->getConvertedId($finder_id));
    }
	
    /**
     * get PartFinder csv base url
     *
     * @return string
     */
    public function getCsvUrl($finder_id, $image_name)
    {
        return $this->getBaseUrl($finder_id) . '/'. $image_name;
    }
    /**
     * get PartFinder csv path from media
     *
     * @return string
     */
    public function getCsvPath($finder_id, $image_name)
    {
        return $this->_subDir.'/csv/'.$this->getConvertedId($finder_id) . '/'. $image_name;
    }
	
	/**
     * get convert product to base64_encode
     *
     * @return string
     */
    public function getConvertedId($finder_id = null) 
    {
		if($finder_id)
		{
			return base64_encode($finder_id);
		}
		else
		{
			return '';
		}
    }
    /**
     * get category tree icon
     *
     * @return string
     */
    public function getImportExampleCsv()
    {
        return $this->_assetRepo->getUrl("Mageants_PartFinder::exmple/import.csv");
    }

}
