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
namespace Webkul\CustomRegistration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_logger;

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
     * @param \Magento\Framework\Logger\Monolog       $logger
     * @param CollectionFactory                       $attributeCollection
     * @param CustomerInterfaceFactory                $customerDataFactory
     * @param DataObjectHelper                        $dataObjectHelper
     * @param \Magento\Eav\Model\Entity               $eavEntity
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param CustomerRepositoryInterface             $customerRepository
     */
    public function __construct(
        \Magento\Framework\Logger\Monolog $logger,
        CollectionFactory $attributeCollection,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_logger = $logger;
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
    }

    /**
     * Customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer['account_controller'];
        $paramData = $data->getRequest();
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $collection = $this->_attributeCollection->create()
            ->setEntityTypeFilter($typeId)
            ->addVisibleFilter()
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        $customData = $paramData->getPostValue();

        $savedCustomerData = $this->_customerRepository->getById($customerId);
        if ($customer->getAddresses() !== null) {
            if ($customer->getId()) {
                $existingAddresses = $this->_customerRepository->getById($customer->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }
        }
        $customer = $this->_customerDataFactory->create();
        $customData = array_merge(
            $this->_customerMapper->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        if (!empty($existingAddressIds) && array_key_exists(0, $existingAddressIds)) {
            $customData[CustomerInterface::DEFAULT_BILLING] = $existingAddressIds[0];
            $customData[CustomerInterface::DEFAULT_SHIPPING] = $existingAddressIds[0];
        }

        $this->_dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->_customerRepository->save($customer);
    }
}
