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

class GetData
{
    /**
     * __construct
     *
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     */
    public function __construct(
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
    ) {
        $this->_eavEntity = $eavEntity;
        $this->_objectManager = $objectManager;
        $this->_attributeCollection = $attributeCollection;
    }

    /**
     * After GetData
     *
     * @param \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject
     * @param array $result
     * @return $result
     */
    public function afterGetData(\Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject, $result)
    {
        if ($result) {
            try {
                $result = $this->getCustomData($result);
            } catch (\Exception $e) {
                return $result;
            }
        }
        return $result;
    }

    /**
     * Get Custom Data
     *
     * @param array $result
     * @return array
     */
    private function getCustomData($result)
    {
        foreach ($result as $customerId => $customerData) {
            $customer = $customerData['customer'];
            $customAttributes = $this->getAttributeCollection($customer['website_id']);
            if ($customAttributes->getSize() > 0) {
                $result[$customerId]['custom_registration'] = [];
                $customAttr = [];
                foreach ($customAttributes as $attribute) {
                    if (isset($customer[$attribute->getAttributeCode()])) {
                        $dateValue = $customer[$attribute->getAttributeCode()];
                        if ($attribute->getFrontendInput() === 'date' && $dateValue) {
                            $customer[$attribute->getAttributeCode()] = date("m/d/Y", strtotime($dateValue));
                        }
                        $customAttr[$attribute->getAttributeCode()] = $customer[$attribute->getAttributeCode()];
                    } else {
                        $customAttr[$attribute->getAttributeCode()] = '';
                    }
                }
                $result[$customerId]['customer'] = $customer;
                $result[$customerId]['custom_registration'] = $customAttr;
            }
        }
        return $result;
    }

    /**
     * Get Attribute Collection
     *
     * @param string $websiteId
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
