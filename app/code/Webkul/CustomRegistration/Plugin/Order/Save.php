<?php
namespace Webkul\CustomRegistration\Plugin\Order;

use Webkul\CustomRegistration\Model\CustomfieldsFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

class Save
{
    /**
     * __construct
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFieldFactory
     * @param \Magento\Framework\Message\ManagerInterface $messagemanager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomfieldsFactory $customFieldFactory,
        ManagerInterface $messagemanager
    ) {
        $this->_customer = $customerRepositoryInterface;
        $this->_customFieldFactory = $customFieldFactory;
        $this->messageManager = $messagemanager;
    }
    
    /**
     * Before Execute
     *
     * Saving customer account information custom attributes value before saving order
     *
     * @param \Magento\Sales\Controller\Adminhtml\Order\Create\Save $subject
     */
    public function beforeExecute(\Magento\Sales\Controller\Adminhtml\Order\Create\Save $subject)
    {
        $orderData = $subject->getRequest()->getPost('order');
        $accountData = $orderData['account'];
        $customerEmail = $accountData['email'];
        try {
            $customerEntity = $this->_customer->get($customerEmail);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Error: %1', $e->getMessage()));
            return;
        }

        foreach ($accountData as $key => $value) {
            if (is_array($value)) {
                if (isset($value['value'])) {
                    $value = $value['value'];
                } else {
                    $value = implode(',', $value);
                }
            }
            $attribute = $customerEntity->getCustomAttribute($key);
            if ($attribute) {
                $attribute->setValue($value);
            } else {
                $customFieldModel = $this->_customFieldFactory->create();
                $collection = $customFieldModel->getCollection()->addFieldToFilter('attribute_code', $key)
                ->setPageSize(1)->setCurPage(1)->getFirstItem();
                if ($collection->getEntityId()) {
                    $customerEntity->setCustomAttribute($key, $value);
                }
            }
        }
        $this->_customer->save($customerEntity);
    }
}
