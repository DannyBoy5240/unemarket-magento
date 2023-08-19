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
namespace Webkul\CustomRegistration\Plugin\Controller;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RedirectFactory
     */
    protected $_redirectUrl;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_entityAttribute;

    /**
     * @var \Webkul\CustomRegistration\Model\CustomfieldsFactory
     */
    protected $customFieldsFactory;

    /**
     * __construct
     *
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param RedirectFactory $redirect
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfieldsFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RedirectFactory $redirect,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfieldsFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_eavEntity = $eavEntity;
        $this->_entityAttribute = $entityAttribute;
        $this->_redirectUrl = $redirect;
        $this->_attributeCollection = $attributeCollection;
        $this->customFieldsFactory = $customfieldsFactory;
    }

    /**
     * Around Execute
     *
     * @param \Magento\Customer\Controller\Account\CreatePost $subject
     * @param Object $proceed
     * @param array $data = "null"
     * @param array $requestInfo = false
     */
    public function aroundExecute(
        $subject,
        $proceed,
        $data = "null",
        $requestInfo = false
    ) {
        $resultRedirect = $subject->resultRedirectFactory->create();

        $refererUrl = explode('?', $subject->_redirect->getRefererUrl())[0];

        $typeId = $this->_eavEntity->setType('customer')->getTypeId();

        $collection = $this->_attributeCollection->create()
            ->setEntityTypeFilter($typeId)
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');

        $error = [];
        $customData = $this->_request->getParams();
        $filesData =  $this->_request->getFiles()->toArray();
        $wholeData = array_merge($customData, $filesData);

        $currentWebsiteId = $this->_storeManager->getWebsite()->getId();
        $customFieldsCollection = $this->customFieldsFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('has_parent', 0)
            ->addFieldToFilter('website_ids', ['like' => '%"'.$currentWebsiteId.'"%']);
        foreach ($customFieldsCollection as $customField) {
            $attribute = $this->getAttributeInfo('customer', $customField->getAttributeCode());
            if ($attribute && $attribute->getId()) {
                $required = explode(' ', $attribute->getFrontendClass());
                if (in_array('required', $required)) {
                    if (empty($wholeData[$attribute->getAttributeCode()])) {
                        $error[] = $attribute->getAttributeCode();
                    }
                }
            }
        }

        foreach ($collection as $attribute) {
            foreach ($customData as $attributeCode => $attributeValue) {
                if ($attributeCode==$attribute->getAttributeCode()) {
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
            $subject->messageManager->addError(
                __(
                    'Please Fill all the Required Fields.'
                )
            );
            $resultRedirect = $this->_redirectUrl->create();
            $resultRedirect->setPath('customer/account/create');
            return $resultRedirect;
        }

        if ($this->getConfigData('enable_registration')) {
            $params = $this->_request->getParams();
            if (!isset($params['account_create_privacy_condition']) ||
                $params['account_create_privacy_condition'] == 0
            ) {
                $subject->messageManager->addError(__('Check Terms and Conditions & Privacy & Cookie Policy.'));
                $resultRedirect = $this->_redirectUrl->create();
                $resultRedirect->setPath('*/*/create', ['_secure' => true]);
                return $resultRedirect;
            }
        }

        // Wallet Address is only available in seller/vendor mode
        if ($wholeData["is_seller"] == "1") {

            // Registrationn form valid and useful wallet address checking with web3
            $address = $wholeData["wallet_addr"];
            // $json = file_get_contents("https://api.etherscan.io/api?module=account&action=balance&address=$address&tag=latest&apikey=KQNSAZ69PHSHNU7FVB786XNZ46JKE2G21Y");
            // $data = json_decode($json, true)

            // $url = 'http://95.217.197.177:80/account/checkethereumaddress';
            // $params = array(
            //     'address' => $address,
            // );
            // $queryString = http_build_query($params);
            // $fullUrl = "$url?$queryString";
            // $options = array(
            //     'http' => array(
            //         'method' => 'GET',
            //         // 'header' => 'Content-type: application/x-www-form-urlencoded',
            //         // 'content' => http_build_query($params),
            //     ),
            // );
            // $context = stream_context_create($options);
            // $response = file_get_contents($fullUrl, false, $context);

            // Set the API endpoint URL
            $url = 'http://167.86.118.183:80/account/checkethereumaddress';

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
                $subject->messageManager->addError(
                    __(
                        'You must input valid and useful Metamask wallet address for your products.'
                    )
                );
                $resultRedirect = $this->_redirectUrl->create();
                $resultRedirect->setPath('customer/account/create');
                return $resultRedirect;
            }

        }

        // Send Request to Mobile Application Backend

        // Set the API endpoint URL
        $url = 'http://167.86.118.183:80/account/marketplaceRequest';

        // Set the JSON payload
        $data = array('email' => $wholeData["email"], 'nickname' => $wholeData["office_name"]);
        $data_string = json_encode($data);

        // Set the cURL options
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
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

        
        $result= $proceed();
        
        // Mobile App Verification Need
        $subject->messageManager->addError(
            __(
                'You must verify your account in mobile app to access Mercado website.'
            )
        );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('\Magento\Customer\Model\Session');
        $customerSession->logout();

        $resultRedirect = $this->_redirectUrl->create();
        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }

    /**
     * Get attribute info by attribute code and entity type
     *
     * @param mixed $entityType can be integer, string, or instance of class Mage\Eav\Model\Entity\Type
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeInfo($entityType, $attributeCode)
    {
        return $this->_entityAttribute->loadByCode($entityType, $attributeCode);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'customer_termandcondition/parameter/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }
}
