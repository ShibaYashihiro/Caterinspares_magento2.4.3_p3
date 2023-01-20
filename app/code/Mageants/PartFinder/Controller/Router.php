<?php
/**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller;

use \Mageants\PartFinder\Model\ResourceModel\PartFinders\CollectionFactory as PartFindersCollectionFactory;
use \Mageants\PartFinder\Helper\Data as PartFinderHelper;

 
class Router implements \Magento\Framework\App\RouterInterface
{
	CONST FINDER_KEY = 'findpart'; 
    /**
     * Data Helper
     *
     * @var \Mageants\PartFinder\Helper\Data
     */
    protected $_helper;
    /**
     * Collection Factory
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\PartFinders\CollectionFactory 
     */
    protected $_partFindersCollectionFactory;
    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
		PartFindersCollectionFactory $partFindersCollectionFactory,
		PartFinderHelper $helper
    ) {
		$this->_partFindersCollectionFactory = $partFindersCollectionFactory;
		
        $this->_helper = $helper;
    }
 
    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
		
		$finder_param = $request->getParams(PartFinderHelper::FINDER_KEY);
		
		$common_urlkey = $this->_helper->getCustomResultUrl();
		
		$identifiers = array_filter(explode('/',trim($request->getPathInfo(),"/")));

		if(count($identifiers) && ($finder_urlkey = $identifiers[0]) !="" )
		{
			$partFindersCollection = $this->_partFindersCollectionFactory->create();
			
			$partfinder = $partFindersCollection->addFieldToFilter('search_result_url',$finder_urlkey)->getFirstItem();;
			
			if(($common_urlkey !="" && $common_urlkey == $finder_urlkey) || ($partfinder->getId() && $partfinder->getStatus()))
			{	
				$this->_helper->setPartFinderRegistry($partfinder);
		
				$request->setModuleName('partfinder')
					->setControllerName('result')
					->setActionName('index');
			}
			else if(( isset($identifiers[0]) && PartFinderHelper::FRONTEND_DEFAULT_ROUTE_NAME == $identifiers[0] ) || ($finder_param && $finder_param!=""))
			{
				$this->_helper->setPartFinderRegistry();
			}
		}
    }
}