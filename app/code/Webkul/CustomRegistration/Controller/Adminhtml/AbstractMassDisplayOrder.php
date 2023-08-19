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

use Webkul\CustomRegistration\Controller\Adminhtml\AbstractMassDisplayEmail;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class AbstractMassStatus
 */
class AbstractMassDisplayOrder extends AbstractMassDisplayEmail
{
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
        $this->setShowInOrder($collection);
        $this->setSuccessMessage($this->setShowInOrder($collection));
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
        $this->setShowInOrder($collection);
        $this->setSuccessMessage($this->setShowInOrder($collection));
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
        $this->setShowInOrder($collection);
        $this->setSuccessMessage($this->setShowInOrder($collection));
    }

    /**
     * Set status to collection items
     *
     * @param AbstractCollection $collection
     * @return void
     */
    protected function setShowInOrder(AbstractCollection $collection)
    {
        $count = 0;
        $attributeModel = $this->_attributeFactory->create();
        foreach ($collection->getAllIds() as $id) {
            $count++;
            /** @var \Magento\Framework\Model\AbstractModel $model */
            $model = $this->_objectManager->get($this->model);
            $model->load($id);
            $attrId = $model->getAttributeId();
            $attributeModel->load($attrId);
            $requiredCheck = $attributeModel->getFrontendClass();
            $require = explode(' ', $requiredCheck);
            // if dependable attribute present.
            if (in_array('dependable_field_'.$attributeModel->getAttributeCode(), $require)) {
                $childAttrModel = $this->_attributeFactory->create();
                $childAttrId = $attrId + 1;
                $childFieldData = $this->customfieldHelper->getChildData($id);
                if (!empty($childFieldData)) {
                    $childAttrId = $childFieldData['attribute_id'];
                }
                $childAttrModel->load($childAttrId);
                $childAttrCode = $childAttrModel->getAttributeCode();
                $childCollection = $this->_objectManager
                                            ->create($this->model)
                                            ->getCollection()
                                            ->addFieldToFilter(
                                                'attribute_code',
                                                $childAttrCode
                                            );
                $childModelId='';
                foreach ($childCollection as $value) {
                    $childModelId = $value->getEntityId();
                }
                $childModel =  $this->_objectManager->create($this->model);
                $childModel->load($childModelId);
                $childModel->setShowInOrder($this->status)->save();
            }
            $model->setShowInOrder($this->status);
            $model->save();
        }
        return $count;
    }
}
