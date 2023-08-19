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
namespace Webkul\CustomRegistration\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\CustomRegistration\Model\ResourceModel\Customfields\CollectionFactory;

/**
 * Controller AbstractMassStatus
 */
class AbstractMassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var bool
     */
    protected $status = true;
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Webkul\CustomRegistration\Helper\Data $customfieldHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Webkul\CustomRegistration\Helper\Data $customfieldHelper
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_attributeFactory = $attributeFactory;
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
            $item->setStatus($this->status);
            $attributeId = $item->getAttributeId();
            $attributeModel->load($attributeId);
            $requiredCheck = $attributeModel->getFrontendClass();
            $require = explode(' ', $requiredCheck);
            /**
             * if dependable attribute presents.
             */
            if (in_array('dependable_field_'.$attributeModel->getAttributeCode(), $require)) {
                $childAttributeModel = $this->_objectManager->get(\Webkul\CustomRegistration\Model\Customfields::class);
                $childAttributeId = $id + 1;
                $childFieldData = $this->customfieldHelper->getChildData($id);
                if (!empty($childFieldData)) {
                    $childAttributeId = $childFieldData['entity_id'];
                }
                $childAttributeModel->load($childAttributeId);
                $childAttributeModel->setStatus($this->status);
                $childAttributeModel->save();
            }
            $item->save();
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $count));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
