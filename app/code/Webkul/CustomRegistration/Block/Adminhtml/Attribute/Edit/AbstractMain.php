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
namespace Webkul\CustomRegistration\Block\Adminhtml\Attribute\Edit;

abstract class AbstractMain extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $yesnoFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $inputTypeFactory;
    /**
     * @var $_attribute
     */
    protected $_attribute = null;

    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Webkul\CustomRegistration\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $eavData = null;

    /**
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Webkul\CustomRegistration\Helper\Data $dataHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Magento\Store\Model\System\Store $systemStore,
        \Webkul\CustomRegistration\Helper\Data $dataHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->propertyLocker = $propertyLocker;
        $this->systemStore = $systemStore;
        $this->dataHelper = $dataHelper;
        $this->eavData = $eavData;
        $this->yesnoFactory = $yesnoFactory;
        $this->inputTypeFactory = $inputTypeFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Return attribute object
     *
     * @return Attribute
     */
    public function getAttributeObject()
    {
        if (null === $this->_attribute) {
            return $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->_attribute;
    }

    /**
     * Set attribute object
     *
     * @param Attribute $attribute
     * @return $this
     */
    public function setattributeObject($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Preparing default form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $modelCustomfield = $this->_coreRegistry->registry('customfields');
        $attributeObj = $this->getAttributeObject();
        $usedInForms = $attributeObj->getUsedInForms();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setHtmlIdPrefix('customfields_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Attribute Properties'),
                'collapsable' => true
            ]
        );
        
        if ($attributeObj->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }
        
        $this->_addElementTypes($fieldset);
        
        $yesno = $this->yesnoFactory->create()->toOptionArray();
        
        $label = $attributeObj->getFrontendLabel();
        $frontendInput = $attributeObj->getFrontendInput();
        $fieldset->addField(
            'attribute_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default label'),
                'title' => __('Default label'),
                'required' => true,
                'value' => is_array($label) ? $label[0] : $label
                ]
        );
            
            $validationClass = sprintf(
                'validate-code validate-length maximum-length-%d',
                \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
            );
            $fieldset->addField(
                'attribute_code',
                'text',
                [
                    'name' => 'attribute_code',
                    'label' => __('Attribute Code'),
                    'title' => __('Attribute Code'),
                    'note' => __(
                        'Make sure you don\'t use spaces or more than %1 characters.',
                        \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                    ),
                    'class' => $validationClass,
                    'required' => true
                    ]
            );
                
                $websiteIds = [];
                $disabled = false;
        if ($modelCustomfield->getWebsiteIds()) {
                    $disabled = true;
                    $websiteIds = $this->dataHelper->jsonDecodeData($modelCustomfield->getWebsiteIds());
        }
                $modelCustomfield->setData('website_ids', $websiteIds);
                $fieldset->addField(
                    'website_ids',
                    'multiselect',
                    [
                    'name'     => 'website_ids',
                    'label'    => __('Select Websites'),
                    'title'    => __('Select Websites'),
                    'required' => true,
                    'value'    => $websiteIds,
                    'values'   => $this->systemStore->getWebsiteValuesForForm(false, false),
                    //   'disabled' => $disabled
                    ]
                );
        
        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Input Type for Store Owner'),
                'title' => __('Input Type for Store Owner'),
                'value' => 'text',
                'values' => $this->getInputType(),
                'required' => true
            ]
        );
        
        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesno
            ]
        );
        if ($frontendInput != 'date') {
            $fieldset->addField(
                'frontend_class',
                'select',
                [
                    'name' => 'frontend_class',
                    'label' => __('Input Validation for Store Owner'),
                    'title' => __('Input Validation for Store Owner'),
                    'values' => $this->getInputTypeForValidation($attributeObj)
                ]
            );
        }
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Input Field Sort Order'),
                'title' => __('Input Field Sort Order'),
                'required' => true
            ]
        );
        $attributeObj->setData('used_in_forms', $usedInForms);
        $fieldset->addField(
            'used_in_forms',
            'multiselect',
            [
                'name' => 'used_in_forms[]',
                'label' => __('Display Fields in Form'),
                'title' => __('Display Fields in Form'),
                'values' => [
                    ['label' => __('Admin Customer Form'), 'value' => 'adminhtml_customer'],
                    ['label' => __('Admin Checkout'), 'value' => 'adminhtml_checkout'],
                    ['label' => __('Customer Account'), 'value' => 'customer_account_edit'],
                    ['label' => __('Registration Form'), 'value' => 'customer_account_create'],
                ],
                'required' => true
            ]
        );
        $fieldset->addField(
            'is_visible',
            'select',
            [
                'name' => 'is_visible',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => [['value' => 1, 'label' => __('Enable')], ['value' => 0, 'label' => __('Disable')]]
            ]
        );
        $fieldset->addField(
            'has_parent',
            'select',
            [
                'name' => 'has_parent',
                'label' => __('Has Parent'),
                'title' => __('Has Parent'),
                'value' => $modelCustomfield->getHasParent(),
                'values' => [['value' => 0, 'label' => __('No')], ['value' => 1, 'label' => __('Yes')]]
            ]
        );

        $this->propertyLocker->lock($form);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->_eventManager->dispatch(
            'adminhtml_block_eav_attribute_edit_form_init',
            ['form' => $this->getForm()]
        );
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Processing block html after rendering Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock(\Magento\Eav\Block\Adminhtml\Attribute\Edit\Js::class)->toHtml();
        return $html . $jsScripts;
    }

    /**
     * Return available input types except texteditor
     *
     * @return  array
     */
    public function getInputType()
    {
        $inputTypes = $this->inputTypeFactory->create()->toOptionArray();
        $notAllowed = ['texteditor', 'datetime', 'pagebuilder'];
        foreach ($inputTypes as $key => $input) {
            $inputTypes[$key]['label'] = __($inputTypes[$key]['label']);
            if (in_array($input['value'], $notAllowed)) {
                unset($inputTypes[$key]);
            }
        }
        return $inputTypes;
    }
    /**
     * Return available input types validations for store owner
     *
     * @param mixed $attributeObj
     * @return array
     */
    public function getInputTypeForValidation($attributeObj)
    {
        $inputTypesForValidation = $this->eavData->getFrontendClasses(
            $attributeObj->getEntityType()->getEntityTypeCode()
        );
        foreach ($inputTypesForValidation as $key => $input) {
            $inputTypesForValidation[$key]['label'] = __($inputTypesForValidation[$key]['label']);
        }
        return $inputTypesForValidation;
    }
}
