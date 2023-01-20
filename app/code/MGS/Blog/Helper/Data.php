<?php

namespace MGS\Blog\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;
	
	protected $_date;
	
	protected $_url;
	
	protected $_filesystem;
	
	protected $_request;
	
	protected $_acceptToUsePanel = false;
	
	protected $_useBuilder = false;
	
	protected $_customer;
	
	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;
	
	/**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
	
    protected $filterManager;
	
	/**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;
	/**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
	
	protected $_file;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    protected $_fullActionName;
	
    protected $_currentCategory;
	
    protected $_currentProduct;
	
    protected $_category;
	
    protected $scopeConfig;
	
	protected $_ioFile;
	
	protected $_moduleManager;
	
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Url $url,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\View\Element\Context $context,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Catalog\Model\Category $category,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
		\Magento\Framework\Filesystem\Io\File $ioFile,
		\Magento\Framework\Xml\Parser $parser,
		\Magento\Framework\Module\Manager $moduleManager,
		CustomerSession $customerSession
	) {
		$this->scopeConfig = $context->getScopeConfig();
		$this->_storeManager = $storeManager;
		$this->_date = $date;
		$this->_url = $url;
		$this->_filesystem = $filesystem;
		$this->customerSession = $customerSession;
		$this->_objectManager = $objectManager;
		$this->_category = $category;
		$this->_request = $request;
		$this->filterManager = $context->getFilterManager();
		$this->_assetRepo = $context->getAssetRepository();
		$this->_blockFactory = $blockFactory;
		$this->_pageFactory = $pageFactory;
		$this->_file = $file;
		$this->_ioFile = $ioFile;
		$this->_moduleManager = $moduleManager;
		$this->_parser = $parser;
		
		$this->_fullActionName = $this->_request->getFullActionName();
		
		if($this->_fullActionName == 'catalog_category_view'){
			$this->_currentCategory = $this->getCurrentCategory();
		}
		
		if($this->_fullActionName == 'catalog_product_view'){
			$this->_currentProduct = $this->getCurrentProduct();
		}
	}
	
	public function getCustomer(){
		if(!$this->_customer){
			$this->_customer = $this->getModel('Magento\Customer\Model\Customer')->load($this->getCustomerId());
		}
		return $this->_customer;
	}
	
	public function getStore(){
		return $this->_storeManager->getStore();
	}
	
	/* Get system store config */
	public function getStoreConfig($node, $storeId = NULL){
		if($storeId != NULL){
			return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
	}

    public function getConfig($key, $store = null)
    {
		return $this->getStoreConfig('blog/' . $key);
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getRoute()
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
        return $this->_storeManager->getStore()->getBaseUrl() . $route;
    }

    public function getTagUrl($tag)
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
        return $this->_storeManager->getStore()->getBaseUrl() . $route . '/tag/' . urlencode($tag);
    }

    public function convertSlashes($tag, $direction = 'back')
    {
        if ($direction == 'forward') {
            $tag = preg_replace("#/#is", "&#47;", $tag);
            $tag = preg_replace("#\\\#is", "&#92;", $tag);
            return $tag;
        }
        $tag = str_replace("&#47;", "/", $tag);
        $tag = str_replace("&#92;", "\\", $tag);
        return $tag;
    }

    public function checkLoggedIn()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn();
    }
	
    public function getThumbnailPost($post)
    {	
		/*$html = "";
		if($post->getVideoThumbId() != ""){
			if($post->getVideoThumbType() == "youtube"){
				$video_url = 'https://www.youtube.com/embed/'.$post->getVideoThumbId();
			}else {
				$video_url = 'https://player.vimeo.com/video/'.$post->getVideoThumbId();
			}
			$html .= '<div class="video-responsive">';
			$html .= '<iframe width="1024" height="768" src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			$html .= '</div>';
		}
        return $html;*/
        $mediaUrl = $this->getBaseMediaUrl(); 
        $html = "";
		if($post->getThumbType() == "video"){

			if(!empty($post->getVideoThumbId()))
			{
			//if($post->getVideoBigId() != ""){
				if($post->getVideoBigType() == "youtube"){
					
						$domain = explode('/', $post->getVideoThumbId());
						if($domain[2] == 'www.youtube.com')
						{
							$id = explode('=', $domain[3])[1]; 
							$video_url = 'https://www.youtube.com/embed/'.$id;
						}
						else if($domain[2] == 'vimeo.com')
						{
							$video_url = 'https://player.vimeo.com/video/'.$domain[3];
						}
				

					
				}

				$html .= '<div class="video-responsive">';
				$html .= '<iframe src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				$html .= '</div>';
			}
			else
			{
				$html .= '<div class="video-responsive">';
				$html .= '<img alt="'.$post->getTitle().'" src="'.$mediaUrl.'mgs_blog/default/default.jpg">';
				$html .= '</div>';
			}
			//}
		}else {
			
			if($post->getThumbnail() == ""){
				$html .= '<div class="video-responsive">';
				$html .= '<img class="image-responsive"  alt="'.$post->getTitle().'" src="'.$mediaUrl.'mgs_blog/default/default.jpg">';
				$html .= '</div>';
			}else {
				$html .= '<div class="video-responsive">';
				$html .= '<img class="image-responsive" alt="'.$post->getTitle().'" src="'.$mediaUrl.$post->getThumbnail().'">';
				$html .= '</div>';
			}
		}
        return $html;
    }
	
	public function getPostUrl($post) {
		$store = $this->_storeManager->getStore()->getCode();
		
		if($store){
			$url = $post->getPostUrlWithNoCategory() . '?___store=' . $store;
		}else{
			$url = $post->getPostUrlWithNoCategory();
		}
		
		return $url;
	}
	
    public function getImagePost($post)
    {	
    	$mediaUrl = $this->getBaseMediaUrl(); 
		$html = "";
		if($post->getThumbType() == "video"){

			if(!empty($post->getVideoThumbId()))
			{
				//if($post->getVideoBigId() != ""){
				if($post->getVideoBigType() == "youtube"){
					$domain = explode('/', $post->getVideoThumbId());
					if($domain[2] == 'www.youtube.com')
					{
						$id = explode('=', $domain[3])[1]; 
						$video_url = 'https://www.youtube.com/embed/'.$id;
					}
					else if($domain[2] == 'vimeo.com')
					{
						$video_url = 'https://player.vimeo.com/video/'.$domain[3];
					}
				}

				$html .= '<div class="video-responsive">';
				$html .= '<iframe src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				$html .= '</div>';
			//}
			}
			else
			{
				$html .= '<div class="image-responsive" class="video-responsive">';
				$html .= '<img class="img-responsive" alt="'.$post->getTitle().'" src="'.$mediaUrl.'mgs_blog/default/default.jpg">';
			}
			
		}else {
			if($post->getImageUrl() == ""){
				$html .= '<div class="video-responsive">';
				$html .= '<img class="image-responsive" alt="'.$post->getTitle().'" src="'.$mediaUrl.'mgs_blog/default/default.jpg">';
				$html .= '</div>';
			}else {
				$html .= '<div class="video-responsive">';
				$html .= '<img class="image-responsive" alt="'.$post->getTitle().'" src="'.$post->getImageUrl().'">';
				$html .= '</div>';
			}
		}
        return $html;
    }
	
	
	public function getThumbnailImgVideoPost($post)
    {	
		if($post->getThumbType() == "video"){
			if($post->getVideoThumbId() != ""){
				if($post->getVideoThumbType() == "youtube"){
					return 'http://img.youtube.com/vi/'.$post->getVideoThumbId().'/hqdefault.jpg';
				}else {
					$info = 'thumbnail_medium';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'vimeo.com/api/v2/video/'.$post->getVideoThumbId().'.php');
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$output = unserialize(curl_exec($ch));
					$output = $output[0][$info];
					curl_close($ch);
					return $output;
				}
			}
			
		}
		return;
    }
	
}
