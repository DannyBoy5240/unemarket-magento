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
namespace Webkul\CustomRegistration\Plugin\Customer;

class Save
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * __construct
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
    ) {
        $this->_request = $request;
        $this->_eavEntity = $eavEntity;
        $this->_objectManager = $objectManager;
        $this->_attributeCollection = $attributeCollection;
    }

    /**
     * Before Execute
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Save $subject
     */
    public function beforeExecute(\Magento\Customer\Controller\Adminhtml\Index\Save $subject)
    {
        $customerData = $this->_request->getPostValue();
        $customer = $customerData['customer'];
        if (isset($customerData['custom_registration'])) {
            $custom_registration = $customerData['custom_registration'];
            $customAttributes = $this->getAttributeCollection($customer['website_id']);
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if (!in_array($attribute->getAttributeCode(), array_keys($custom_registration))) {
                        $custom_registration[$attribute->getAttributeCode()] = '';
                    }
                }
                if (!empty($custom_registration)) {
                    foreach ($custom_registration as $key => $value) {
                        $customer[$key] = $value;
                    }
                }
            }
        } else {
            $customAttributes = $this->getAttributeCollection();
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if (in_array($attribute->getAttributeCode(), array_keys($customer))) {
                        $customer[$attribute->getAttributeCode()] = '';
                    }
                }
            }
        }
        $customerData['customer'] = $customer;
        $this->_request->setPostValue($customerData);
    }

    /**
     * Get Attribute Collection
     *
     * @param int $websiteId
     * @return \Magento\Customer\Model\ResourceModel\Attribute\Collection
     */
    private function getAttributeCollection($websiteId = null)
    {
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $query = 'ccp.status = 1';
        if ($websiteId) {
            $query .= ' AND (ccp.website_ids LIKE \'%'.'"'.$websiteId.'"'.'%\' OR ccp.website_ids LIKE \'%"0"%\')';
        }
        $customField = $this->_attributeCollection->create()->getTable('wk_customfields');

        $collection = $this->_attributeCollection->create()
                ->setEntityTypeFilter($typeId)
                ->setOrder('sort_order', 'ASC');
        $collection->getSelect()
        ->join(
            ["ccp" => $customField],
            "ccp.attribute_id = main_table.attribute_id",
            ["status" => "status"]
        )->where($query);
        return $collection;
    }
}
