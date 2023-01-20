<?php
    namespace Customization\Customer\Observer;
    use Magento\Framework\Event\ObserverInterface;
    class AfterSaveCustomerAdmin implements ObserverInterface
    {
        protected $customer;

        protected $customerFactory;
        protected $_request;
    /**
    * @var \Magento\Framework\App\ResponseFactory
    */
   

    public function __construct(
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Framework\App\RequestInterface $request)
    {
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   

        $post = $this->_request->getPost();
        $event = $observer->getEvent();
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();

        $customer = $this->customer->load($customerId);

        $customerData = $customer->getDataModel();
        $customerData->setCustomAttribute('company1',$customer->getCompany1());
        $customer->updateData($customerData);
        $customerResource = $this->customerFactory->create();
        $customerResource->saveAttribute($customer, 'company1');
    }
}