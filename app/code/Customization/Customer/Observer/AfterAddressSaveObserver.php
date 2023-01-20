<?php
    namespace Customization\Customer\Observer;
    use Magento\Framework\Event\ObserverInterface;

    class AfterAddressSaveObserver implements ObserverInterface
    {
        protected $customer;

        protected $customerFactory;
        protected $_request;
        protected $_logger;
        protected $addressRepository;
        protected $_addressConfig;
        protected $_addressMapper;
    /**
    * @var \Magento\Framework\App\ResponseFactory
    */
   

    public function __construct(
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper
    )
    {
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $addressId = $this->_request->getParam('id');
        if(!$addressId)
        {
            $customerAddressId = $observer->getEvent()->getCustomerAddress()->getEntityId();

            try {
                $address = $this->addressRepository->getById($customerAddressId);
                $post = $this->_request->getPost();
                $address->setCompany($post['company']);
                 $this->addressRepository->save($address);
                /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
                /*$renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
                $data = $renderer->renderArray($this->addressMapper->toFlatArray($addressObject));
                $this->_logger->info("address : -- ".$data); */
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
        }
        

        /*$addressObject = $this->addressRepository->getById($customerAddressId);
        $this->_logger->info("address : -- ".$addressObject);  */
        /*$post = $this->_request->getPost();
        echo '<pre>';
        print_r($post);die;

        $post = $this->_request->getPost();
        $event = $observer->getEvent();
        $customer = $observer->getCustomerDataObject();
        $customerId = $customer->getId();

        $customer = $this->customer->load($customerId);

        $customerData = $customer->getDataModel();
        $customerData->setCustomAttribute('company1',$post['company']);
        $customer->updateData($customerData);
        $customerResource = $this->customerFactory->create();
        $customerResource->saveAttribute($customer, 'company1');*/
    }
}