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

use Magento\Backend\App\Action;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Controller AbstractMassDisplayEmail
 */
class AbstractMassDisplayEmail extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    public const ID_FIELD = 'entity_id';

    /**
     * Contain Redirect url
     */
    public const REDIRECT_URL = '*/*/';

    /**
     * @var string
     */
    protected $collection = \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class;

    /**
     * @var string
     */
    protected $model = \Magento\Framework\Model\AbstractModel::class;

    /**
     * @var bool
     */
    protected $status = true;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\CustomRegistration\Helper\Data
     */
    protected $customfieldHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Webkul\CustomRegistration\Helper\Data $customfieldHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Webkul\CustomRegistration\Helper\Data $customfieldHelper
    ) {
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
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        try {
            if (isset($excluded)) {
                if (!empty($excluded) && $excluded != 'false') {
                    $this->excludedSetStatus($excluded);
                } else {
                    $this->setStatusAll();
                }
            } elseif (!empty($selected)) {
                $this->selectedSetStatus($selected);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL);
    }

    /**
     * Set status to all
     *
     * @return void
     * @throws \Exception
     */
    protected function setStatusAll()
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $this->setShowInEmail($collection);
        $this->setSuccessMessage($this->setShowInEmail($collection));
    }

    /**
     * Set status to all but the not selected
     *
     * @param array $excluded
     * @return void
     * @throws \Exception
     */
    protected function excludedSetStatus(array $excluded)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
        $this->setShowInEmail($collection);
        $this->setSuccessMessage($this->setShowInEmail($collection));
    }

    /**
     * Set status to selected items
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedSetStatus(array $selected)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setShowInEmail($collection);
        $this->setSuccessMessage($this->setShowInEmail($collection));
    }

    /**
     * Set status to collection items
     *
     * @param AbstractCollection $collection
     * @return void
     */
    protected function setShowInEmail(AbstractCollection $collection)
    {
        $count = 0;
        $attributeModel = $this->_attributeFactory->create();
        foreach ($collection->getAllIds() as $id) {
            $count++;
            /** @var \Magento\Framework\Model\AbstractModel $model */
            $model = $this->_objectManager->get($this->model);
            $model->load($id);
            $attributeId = $model->getAttributeId();
            $attributeModel->load($attributeId);
            $requiredCheck = $attributeModel->getFrontendClass();
            $require = explode(' ', $requiredCheck);
            // if dependable attribute present.
            if (in_array('dependable_field_'.$attributeModel->getAttributeCode(), $require)) {
                $childAttributeModel = $this->_attributeFactory->create();
                $childAttributeId = $attributeId + 1;
                $childFieldData = $this->customfieldHelper->getChildData($id);
                if (!empty($childFieldData)) {
                    $childAttributeId = $childFieldData['attribute_id'];
                }
                $childAttributeModel->load($childAttributeId);
                $childAttributeCode = $childAttributeModel->getAttributeCode();
                $childModelCollection = $this->_objectManager
                                                ->create($this->model)
                                                ->getCollection()
                                                ->addFieldToFilter(
                                                    'attribute_code',
                                                    $childAttributeCode
                                                );
                $childModelId='';
                foreach ($childModelCollection as $value) {
                    $childModelId = $value->getEntityId();
                }
                $childModel =  $this->_objectManager->create($this->model);
                $childModel->load($childModelId);
                $childModel->setShowInEmail($this->status)->save();
            }
            $model->setShowInEmail($this->status);
            $model->save();
        }
        return $count;
    }

    /**
     * Set error messages
     *
     * @param int $count
     * @return void
     */
    protected function setSuccessMessage($count)
    {
        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been updated.',
                $count
            )
        );
    }
}
