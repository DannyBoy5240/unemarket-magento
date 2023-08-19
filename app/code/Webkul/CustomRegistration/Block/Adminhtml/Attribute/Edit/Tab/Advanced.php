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
namespace Webkul\CustomRegistration\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Helper\Data;

class Advanced extends Generic
{
      
    /**
     * @var $_eavData
     */
    protected $_eavData = null;

    /**
     * @var $_yesNo
     */
    protected $_yesNo;
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $_inputTypeFactory;

    /**
     * @var array
     */
    protected $disableScopeChangeList;
    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfields
     * @param Yesno $yesNo
     * @param Data $eavData
     * @param array $disableScopeChangeList = ['sku']
     * @param array $data = []
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Webkul\CustomRegistration\Model\CustomfieldsFactory $customfields,
        Yesno $yesNo,
        Data $eavData,
        array $disableScopeChangeList = ['sku'],
        array $data = []
    ) {
        $this->_yesNo = $yesNo;
        $this->_eavData = $eavData;
        $this->propertyLocker = $propertyLocker;
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->disableScopeChangeList = $disableScopeChangeList;
        $this->customfields = $customfields;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
  
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('dependable_');

        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Dependable Fields Properties'), 'collapsable' => true]
        );

        $customerAttrColl = $this->customfields->create()->getCollection()
                                ->addFieldToFilter('wk_frontend_input', ['in' => ['select', 'radio']]);
        $attributeList = [
            ['label' => __('Select Parent Attribute'), 'value' => '']
        ];
        foreach ($customerAttrColl as $attribute) {
            $attributeList[] = [
                'label' => $attribute->getAttributeLabel(),
                'value' => $attribute->getAttributeId()
            ];
        }
        $fieldset->addField(
            'attribute_code',
            'select',
            [
                'name' => 'parent_attr_id',
                'label' => __('Parent Attribute'),
                'title' => __('Parent Attribute'),
                'required' => true,
                'values' => $attributeList
            ]
        );
        $options = [['value' => '', 'label' => __('Select Option')]];
        $tempData = $this->getAttributeObject()->getData();
        if (isset($tempData['parent_attr_id']) && $tempData['parent_attr_id']) {
            $options = $this->attributeFactory->create()->load($tempData['parent_attr_id'])
                                         ->getSource()->getAllOptions(false);
        }
        $fieldset->addField(
            'attribute_opt',
            'select',
            [
                'name' => 'parent_attr_opt_id',
                'label' => __('Parent Attribute Options'),
                'title' => __('Parent Attribute Options'),
                'required' => true,
                'values' => $options
            ]
        );

        $this->propertyLocker->lock($form);
        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $tempData = $this->getAttributeObject()->getData();
        $data = [
            'attribute_code' => $tempData['parent_attr_id'] ?? '',
            'attribute_opt' => $tempData['parent_attr_opt_id'] ?? ''
        ];
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('customfields');
    }
}
