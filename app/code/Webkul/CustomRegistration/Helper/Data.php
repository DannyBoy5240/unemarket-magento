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
namespace Webkul\CustomRegistration\Helper;

use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Custom Registration Orders helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var string
     */
    protected $_eavEntity;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Session $customerSession
     * @param JsonHelper $jsonHelper
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfield
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Session $customerSession,
        JsonHelper $jsonHelper,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfield
    ) {
        $this->_storeManager = $storeManager;
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->customfield = $customfield;
        parent::__construct($context);
    }

    /**
     * This function will decode the array to json format
     *
     * @param array $data
     * @return json
     */
    public function jsonDecodeData($data)
    {
        return $this->jsonHelper->jsonDecode($data);
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }

    /**
     * This function will return the id of the current store
     *
     * @return Integer
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getWebsite()->getId();
    }

    /**
     * This function will return the id of the current store
     *
     * @return Integer
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Child attribute customfield table data from custom field id
     *
     * @param int $customfieldId
     * @return array
     */
    public function getChildData($customfieldId)
    {
        $childData = [];
        $collection = $this->customfield->create()
            ->getCollection()
            ->addFieldToFilter('has_parent', $customfieldId);
        foreach ($collection as $model) {
            $childData = $model->getData();
        }
        return $childData;
    }
     /**
     * Get All root custom fields
     *
     * @return array
     */
    public function getAllCustomFields()
    {
        $allCustomFields = $this->customfield->create()->getCollection()->addFieldToFilter('status',1)->getData();
        $arrayRender = [];
        $rootFields = [];
        foreach($allCustomFields as $key=>$value){
            if(!$value['has_parent']){
                array_push($rootFields, $value);
            }
        }
        foreach($rootFields as $k=>$v){
            $rootFields[$k]['children_fields'] = $this->getChildrenFields($v['attribute_id']);
        }
        return $rootFields;
    }
     /**
     * Get All children/sub-children of a parent field
     *
     * @param int $attr_id
     * @return array
     */
    public function getChildrenFields($attr_id)
    {
        $parentField = $this->customfield->create()->getCollection()->addFieldToFilter('parent_attr_id',$attr_id)->addFieldToFilter('status',1)->getData();
        foreach($parentField as $key=>$value){
            $parentField[$key]['children_fields'] = $this->getChildrenFields($value['attribute_id']);
        }
        return $parentField;
    }

}
