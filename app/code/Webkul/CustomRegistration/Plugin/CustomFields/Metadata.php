<?php
namespace Webkul\CustomRegistration\Plugin\CustomFields;

use Webkul\CustomRegistration\Model\CustomfieldsFactory;

class Metadata
{
    /**
     * __construct
     *
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFieldFactory
     */
    public function __construct(CustomfieldsFactory $customFieldFactory)
    {
        $this->_customFieldFactory = $customFieldFactory;
    }

    /**
     * After GetUserAttributes
     *
     * @param \Magento\Customer\Model\Metadata\Form $subject
     * @param array $result
     * @return array $result
     */
    public function afterGetUserAttributes(\Magento\Customer\Model\Metadata\Form $subject, $result)
    {
        $customFieldModel = $this->_customFieldFactory->create();
        foreach ($result as $key => $value) {
            $collection = $customFieldModel->getCollection()->addFieldToFilter('attribute_code', $key)->getFirstItem();
            $id = $collection->getEntityId();
            $status  = $collection->getStatus();
            if (!$id || !$status) {
                unset($result[$key]);
            }
        }
        return $result;
    }
}
