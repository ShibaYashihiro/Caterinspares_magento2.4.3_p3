<?php
 /**
 * @category  Mageants Part Finder
 * @package   Mageants_PartFinder
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <info@mageants.com>
 */
namespace Mageants\PartFinder\Controller\Adminhtml\PartFinders;

use \Mageants\PartFinder\Model\PartFindersFactory;
use \Mageants\PartFinder\Model\PartFinderOptionsFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
use \Mageants\PartFinder\Helper\Data;
use \Mageants\PartFinder\Model\Upload;
use \Mageants\PartFinder\Model\ResourceModel\Image; 
		
class Save extends \Mageants\PartFinder\Controller\Adminhtml\PartFinders
{
	/**
     * Access Resource ID
     * 
     */
	const RESOURCE_ID = 'Mageants_PartFinder::partfinders_save';
	 /**
     * Upload model
     * 
     * @var \Mageants\PartFinder\Model\Upload
     */
    protected $_uploadModel;

    /**
     * Image model
     * 
     * @var \Mageants\PartFinder\Model\ResourceModel\Image
     */
    protected $_imageModel;
    
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;
	
    /**
     * PartFinder Data Helper
     * 
     * @var \Mageants\PartFinder\Helper\Data
     */
    protected $_partfinderHelper; 
    /**
     * PartFinder Data Helper
     * 
     * @var \Mageants\PartFinder\Model\PartFinderOptionsFactory
     */
    protected $_partFinderOptionsFactory; 
	
    /**
     * constructor
     * 
     * @param Upload $uploadModel
     * @param File $fileModel
     * @param Image $imageModel
     * @param Session $backendSession
     * @param PartFindersFactory $partfindersFactory
     * @param Registry $registry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        PartFindersFactory $partfindersFactory,
        PartFinderOptionsFactory $partFinderOptionFactory,
        Registry $registry,
        Context $context,
		Data $partfinderHelper
    )
    {
        $this->_backendSession = $context->getSession();
		
		$this->_partfinderHelper = $partfinderHelper;		
		
		$this->_partFinderOptionsFactory = $partFinderOptionFactory;		
		
        parent::__construct($partfindersFactory, $registry, $context);

    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
		$partfinder = $this->_initPartFinder();
			
        $data = $this->getRequest()->getPost('partfinder');
        
        $resultRedirect = $this->resultRedirectFactory->create();
		if ($data) 
		{
			if(!$partfinder->getId())
			{
				$data['created_at'] = date('Y-m-d H:i:s');				
			}
			
			$data['updated_at'] = date('Y-m-d H:i:s');
			
            $partfinder->setData($data);
			
            $this->_eventManager->dispatch(
                'mageants_partfinder_partfinder_prepare_save',
                [
                    'partfinder' => $partfinder,
                    'request' => $this->getRequest()
                ]
            );
			
            try 
			{
                $partfinder->save();
				
				if(!isset($data['id']))
				{
					$tmp = $data['filter_option_count'];
					
					for($i = 0 ; $i < $tmp ; $i++ )
					{
						$optionsFactory = $this->_partFinderOptionsFactory->create();
						
						$optData['finder_id'] = $partfinder->getId();
						
						$optionsFactory->setData($optData);
						
						$optionsFactory->save();
					}
				}
				else
				{					
					$options_data = $this->getRequest()->getPost('finderoptions');
				
					if(is_array($options_data['name']))
					{
						foreach($options_data['name'] as $opt_id=>$opt_data)
						{
							$optionsFactory = $this->_partFinderOptionsFactory->create();
							
							$optionsFactory->setId($opt_id);
							
							$optionsFactory->setName($options_data['name'][$opt_id]);
							
							$optionsFactory->setSortOrder($options_data['sort_order'][$opt_id]);
							
							//$optionsFactory->setIsRange($options_data['is_range'][$opt_id]);
							
							$optionsFactory->save();
						}
					}
				}
				
                $this->messageManager->addSuccess(__('The Part Finder has been saved.'));
				
                $this->_backendSession->setMageantsPartFinderData(false);
				
                if ($this->getRequest()->getParam('back')) 
				{
                    $resultRedirect->setPath(
                        'mageants_partfinder/*/edit',
                        [
                            'id' => $partfinder->getId(),
                            '_current' => true
                        ]
                    );
					
                    return $resultRedirect;
                }
				
                $resultRedirect->setPath('mageants_partfinder/*/');
				
                return $resultRedirect;
				
            } 
			catch (\Magento\Framework\Exception\LocalizedException $e) 
			{
                $this->messageManager->addError($e->getMessage());
            } 
			catch (\RuntimeException $e) 
			{
                $this->messageManager->addError($e->getMessage());
            } 
			catch (\Exception $e) 
			{
                $this->messageManager->addException($e, __('Something went wrong while saving the Part Finder.'));
            }
			
            $this->_getSession()->setMageantsPartFinderPostData($data);
			
            $resultRedirect->setPath(
                'mageants_partfinder/*/edit',
                [
                    'id' => $partfinder->getId(),
                    '_current' => true
                ]
            );
			
            return $resultRedirect;
        }
		
        $resultRedirect->setPath('mageants_partfinder/*/');
		
        return $resultRedirect;
    }
	
	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed(Self::RESOURCE_ID);
	}
	
}
