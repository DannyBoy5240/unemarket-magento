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
namespace Webkul\CustomRegistration\Controller\Adminhtml\Customfields;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\CustomRegistration\Model\ResourceModel\Customfields\CollectionFactory;

/**
 * Controller MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Webkul\CustomRegistration\Model\Customfields $customFields
     * @param \Webkul\CustomRegistration\Helper\Data $customfieldHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Webkul\CustomRegistration\Model\Customfields $customFields,
        \Webkul\CustomRegistration\Helper\Data $customfieldHelper
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->customFields = $customFields;
        $this->customfieldHelper = $customfieldHelper;
        parent::__construct($context);
    }
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                        ->isAllowed(
                            'Webkul_CustomRegistration::customregistration'
                        );
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        $attributeModel = $this->_attributeFactory->create();
        $count = 0;
        foreach ($collection as $item) {
            $id = $item->getEntityId();
            $attributeModel->load($item->getAttributeId());
            $requiredCheck = $attributeModel->getFrontendClass();
            $attributeCode = $attributeModel->getAttributeCode();
            $require = explode(' ', $requiredCheck);
            $attributeModel->delete();
            $item->delete();
            /**
             * if dependable attribute presents.
             */
            if (in_array('dependable_field_'.$attributeCode, $require)) {
                $childAttributeId = $id + 1;
                $childFieldData = $this->customfieldHelper->getChildData($id);
                if (!empty($childFieldData)) {
                    $childAttributeId = $childFieldData['entity_id'];
                }
                $childAttributeModel = $this->customFields->load($childAttributeId);
                $attributeModel->load($childAttributeModel->getAttributeId());
                $attributeModel->delete();
                $childAttributeModel->delete();
            }
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
