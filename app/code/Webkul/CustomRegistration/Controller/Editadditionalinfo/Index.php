<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Controller\Editadditionalinfo;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_eavEntity;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;
     /**
      * @var \Magento\Customer\Model\Customer\Mapper
      */
    protected $_customerMapper;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_attributeCollection = $attributeCollection;
        $this->customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_eavEntity = $eavEntity;
        parent::__construct($context);
    }

    /**
     * Save customer info
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $paramData = $this->getRequest();
        $customerId = $this->customerSession->getCustomerId();
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $collection = $this->_attributeCollection->create()
            ->setEntityTypeFilter($typeId)
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        $error = [];
        $customData = $paramData->getPostValue();
        foreach ($collection as $attribute) {
            foreach ($customData as $attributeCode => $attributeValue) {
                if ($attributeCode == $attribute->getAttributeCode()) {
                    $required = explode(' ', $attribute->getFrontendClass());

                    if (in_array('required', $required)) {
                        if (empty($attributeValue)) {
                            $error[] = $attribute->getAttributeCode();
                        }
                    }
                }
            }
        }
        if (!empty($error)) {
            $this->messageManager->addError(__('Please Fill all the Required Fields.'));
            $resultRedirect->setPath('*/customfields/');
            return $resultRedirect;
        }

        // check inputed wallet address is valid
        $address = $customData["wallet_addr"];
        // $json = file_get_contents("https://api.etherscan.io/api?module=account&action=balance&address=$address&tag=latest&apikey=KQNSAZ69PHSHNU7FVB786XNZ46JKE2G21Y");
        // $data = json_decode($json, true);
        // if (!($data['status'] == 1 && $data['result'] > 0)) {

        // Set the API endpoint URL
        $url = 'http://95.217.197.177:80/account/checkethereumaddress';

        // Set the JSON payload
        $data = array('address' => $address);
        $data_string = json_encode($data);

        // Set the cURL options
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        // Send the API request and get the response
        $response = curl_exec($ch);

        // Close the cURL session
        curl_close($ch);

        $jsonObject = json_decode($response);
        if (!($jsonObject && $jsonObject->success)) {
            $this->messageManager->addError(__('You must input valid and useful Metamask wallet address for your products.'));
            $resultRedirect->setPath('*/customfields/');
            return $resultRedirect;
        }

        $savedCustomerData = $this->_customerRepository->getById($customerId);
        $saveData = $this->_customerMapper->toFlatArray($savedCustomerData);

        $customer = $this->_customerDataFactory->create();
        $customData = array_merge(
            $this->_customerMapper->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        $this->_dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->_customerRepository->save($customer);
        $this->messageManager->addSuccess(__('Customer additional information saved successfully.'));
        return $resultRedirect->setPath('*/customfields/');
    }
}
