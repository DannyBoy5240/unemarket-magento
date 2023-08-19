<?php

namespace Webkul\CustomRegistration\Plugin\Export;

use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Framework\Api\Search\DocumentInterface;

class ExportRowDataModification
{
    /**
     * __construct
     *
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Attribute $attributeCollection
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfieldsFactory
     */
    public function __construct(
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Attribute $attributeCollection,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfieldsFactory
    ) {
        $this->_customFieldFactory = $customfieldsFactory;
        $this->_eavEntity = $eavEntity;
        $this->_attributeCollection = $attributeCollection;
    }
    
    /**
     * After GetRowData
     *
     * Returns row data
     *
     * @param MetadataProvider $subject
     * @param array $result
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     *
     * @return string[] $result
     */
    public function afterGetRowData(MetadataProvider $subject, $result, $document, $fields, $options)
    {
        $i = 0;
        foreach ($fields as $column) {
            if (!isset($options[$column])) {
                $collection = $this->_customFieldFactory->create();
                $collection = $collection->getCollection()->addFieldToFilter('attribute_code', $column)->getFirstItem();
                $id = $collection->getEntityId() ? $collection->getEntityId() : null;
                if ($id) {
                    $optionId = $document->getCustomAttribute($column)->getValue();
                    $label = $this->retrieveOptions($column, $optionId);
                    if ($label!==false) {
                        if (is_array($label)) {
                            $label = implode(",", $label);
                        }
                        $result[$i] = $label;
                    }
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * Retrieve Option Label
     *
     * @param string $attrCode
     * @param int $optionId
     *
     * @return string
     */
    public function retrieveOptions($attrCode, $optionId)
    {
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $attribute = $this->_attributeCollection->loadByCode($typeId, $attrCode);
        if ($attribute->getFrontendInput()=='boolean') {
            $optionLabel = $optionId ? 'Yes' : 'No';
        } else {
            $optionLabel = $attribute->getSource()->getOptionText($optionId);
        }
        return $optionLabel;
    }
}
