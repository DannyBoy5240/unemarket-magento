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
namespace Webkul\CustomRegistration\Block;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;

class Customfields extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFields
     */
    private $customFields;

    /**
     * @var \Webkul\CustomRegistration\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param CustomerFactory $customer
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param Session $session
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFields
     * @param \Webkul\CustomRegistration\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        CustomerFactory $customer,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        Session $session,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFields,
        \Webkul\CustomRegistration\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->_attributeFactory = $attributeFactory;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->_urlEncoder = $urlEncoder;
        $this->_urlBuilder = $urlBuilder;
        $this->customFields = $customFields;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Get current customer info.
     *
     * @return object
     */
    public function getCurrentCustomer()
    {
        $customerId = $this->_session->getCustomer()->getId();
        $customerData = $this->_customer->create()->load($customerId);
        return $customerData;
    }

    /**
     * Get UsedInForms
     *
     * @param  int $id
     * @return array
     */
    public function getUsedInForms($id)
    {
        $attributeModel = $this->_attributeFactory->create();
        return $attributeModel->load($id)->getUsedInForms();
    }

    /**
     * Encode FileName
     *
     * @param string $type
     * @param string $filePath
     * @return string
     */
    public function encodeFileName($type, $filePath)
    {
        $url = $this->_urlBuilder->getUrl(
            '*/media/view',
            [$type => $this->_urlEncoder->encode(ltrim($filePath, '/'))]
        );
        return $url;
    }

    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->_session->getCustomerFormData(true);
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Has DependentFields
     *
     * @param int $attrId
     * @return string
     */
    public function hasDependentFields($attrId)
    {
        $currentWebsiteId = $this->dataHelper->getCurrentWebsiteId();
        $dependentFields = $this->customFields->create()->getCollection()
                                ->addFieldToFilter('parent_attr_id', ['eq' => $attrId])
                                ->addFieldToFilter('status', ['eq' => 1])
                                ->addFieldToFilter('website_ids', ['like' => '%"'.$currentWebsiteId.'"%'])
                                ->setPageSize(1)->setCurPage(1)->getFirstItem();
        return $dependentFields->getEntityId() ? 'dependent_fields' : '';
    }

    /**
     * Get ScriptJsonData
     *
     * @return JSON
     */
    public function getScriptJsonData()
    {
        $data = [
            'dependentFieldUrl' => $this->getUrl('customregistration\dependent\fields'),
            'customerData' => $this->getCurrentCustomer()->toArray()
        ];
        return $this->dataHelper->jsonEncodeData($data);
    }

    /**
     * Attribute CollectionFilter
     *
     * @return [type] [description]
     */
    public function attributeCollectionFilter()
    {
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $currentWebsiteId = $this->dataHelper->getCurrentWebsiteId();
        $query = 'ccp.has_parent = 0 AND ccp.status = 1';
        $query .= ' AND (ccp.website_ids LIKE \'%'.'"'.$currentWebsiteId.'"'.'%\' OR ccp.website_ids LIKE \'%"0"%\')';
        $customField = $this->customFields->create()->getCollection()->getTable('wk_customfields');
        $collection = $this->_attributeCollection->create()
                ->setEntityTypeFilter($typeId)
                ->setOrder('sort_order', 'ASC');

        $collection->getSelect()
        ->join(
            ["ccp" => $customField],
            "ccp.attribute_id = main_table.attribute_id",
            ["status" => "status","wk_frontend_input" => "wk_frontend_input"]
        )->where($query);

        return $collection;
    }
    public function getHelper()
    {
        return $this->dataHelper;
    }
}
