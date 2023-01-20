<?php

namespace Olegnax\Athlete2Slideshow\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class MassStatus extends Action
{

    const ADMIN_RESOURCE = 'Olegnax_Athlete2Slideshow::Slides_Edit';

    public $filter;

    public $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Olegnax\Athlete2Slideshow\Model\ResourceModel\Slides\CollectionFactory $collectionFactory
    )
    {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        
        parent::__construct($context);
    }

    public function execute()
    {
        $collection    = $this->filter->getCollection($this->collectionFactory->create());
        $status        = (int)$this->getRequest()->getParam('status');
        $sliderUpdated = 0;
        foreach ($collection as $slider) {
            try {
                $slider->setStatus($status)
                    ->save();

                $sliderUpdated++;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating status for %1.', $slider->getName()));
            }
        }

        if ($sliderUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $sliderUpdated));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
