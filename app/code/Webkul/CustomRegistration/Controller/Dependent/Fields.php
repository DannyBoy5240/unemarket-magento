<?php
/**
 * @category   Webkul
 * @package    Webkul_CustomRegistration
 * @author     Webkul Software Private Limited
 * @copyright   Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Controller\Dependent;

use Magento\Framework\Controller\ResultFactory;

class Fields extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Eav\Model\Entity
     */
    private $eavEntity;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Webkul\CustomRegistration\Model\CustomfieldsFactory
     */
    private $customFields;

    /**
     * @var \Webkul\CustomRegistration\Helper\Data
     */
    private $dataHelper;

    /**
     * ValidateTest constructor.
     *
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFields
     * @param \Webkul\CustomRegistration\Helper\Data $dataHelper
     * @param \Webkul\CustomRegistration\Helper\Order $orderData
     */
    public function __construct(
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customFields,
        \Webkul\CustomRegistration\Helper\Data $dataHelper,
        \Webkul\CustomRegistration\Helper\Order $orderData
    ) {
        parent::__construct($context);
        $this->eavEntity = $eavEntity;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeFactory = $attributeFactory;
        $this->timezone = $timezone;
        $this->customFields = $customFields;
        $this->dataHelper = $dataHelper;
        $this->orderData = $orderData;
    }
    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('customregistration/*/');
        }
        $customerData = isset($data['customerId']) ? $this->orderData->getCurrentCustomer($data['customerId']) : false;
        $typeId = $this->eavEntity->setType('customer')->getTypeId();
        $currentWebsiteId = $this->dataHelper->getCurrentWebsiteId();
        $dependentFields = $this->customFields->create()->getCollection()
                                    ->addFieldToFilter('has_parent', ['eq' => 1])
                                    ->addFieldToFilter('status', ['eq' => 1])
                                    ->addFieldToFilter('parent_attr_id', ['eq' => $data['attr_id']])
                                    ->addFieldToFilter('parent_attr_opt_id', ['eq' => $data['opt_id']])
                                    ->addFieldToFilter(
                                        'website_ids',
                                        [['like' => '%"0"%'], ['like' => '%"'.$currentWebsiteId.'"%']]
                                    )->getColumnValues('attribute_id');

        $attributeList = $this->attributeFactory->create()
                            ->addFieldToFilter('main_table.attribute_id', ['in' => $dependentFields])
                            ->setOrder('additional_table.sort_order', 'ASC');
        $dependentFieldList = [];
        foreach ($attributeList as $attribute) {
            $isRequiredArray = explode(' ', $attribute->getFrontendClass());
            $filePath = '';
            $options = in_array($attribute->getFrontendInput(), ['select', 'multiselect']) ?
                    $attribute->getSource()->getAllOptions(false) : [];
            $value = $customerData ? $customerData->getData($attribute->getAttributeCode()) : '';
            if ($customerData && in_array($attribute->getFrontendInput(), ['file', 'image'])) {
                $filePath = $this->orderData->encodeFileName(
                    $attribute->getFrontendInput(),
                    $value
                );
            } elseif ($customerData && $attribute->getFrontendInput() == 'date') {
                $value = $this->timezone->date(new \DateTime($value))->format('m/d/Y');
            }
            $attribute = $attribute->getData();
            $attribute['options'] = $options;
            $attribute['value'] = $value;
            $attribute['filePath'] = $filePath;
            $attribute['isRequired'] = in_array('required', $isRequiredArray);
            $attribute['custom'] = $this->getDependentFiledsData($attribute['attribute_id']);
            $dependentFieldList[] = $attribute;
        }
        $dependentFieldList = [
            'optId' => $data['opt_id'],
            'totalRecords' => count($dependentFieldList),
            'fields' => $dependentFieldList
        ];
        return $this->resultJsonFactory->create()->setData($dependentFieldList);
    }

    /**
     * GetDependentFiledsData
     *
     * @param int $attribureId
     * @return array
     */
    private function getDependentFiledsData($attribureId)
    {
        $dependentFields = $this->customFields->create()->getCollection()
                                    ->addFieldToFilter('attribute_id', ['eq' => $attribureId])
                                    ->setPageSize(1)->setCurPage(1)->getFirstItem();
        $childList = $this->customFields->create()->getCollection()
                                    ->addFieldToFilter('parent_attr_id', ['eq' => $attribureId]);
        return [
            'custom_type' => $dependentFields->getWkFrontendInput(),
            'dependent_fields' => $childList->getSize() ? 'dependent_fields' : ''
        ];
    }
}
