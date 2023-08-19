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
namespace Webkul\CustomRegistration\Block\Adminhtml\Attribute;

/**
 * Product attribute edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Webkul_CustomRegistration';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_attribute';

        parent::_construct();

        $this->addButton(
            'save_and_edit_button',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ]
        );

        $this->buttonList->update('save', 'label', __('Save Attribute'));
        $this->buttonList->update('save', 'class', 'save primary');
        $this->buttonList->update('reset', 'label', __('Reset'));
        $entityAttribute = $this->_coreRegistry->registry('entity_attribute');
        if (!$entityAttribute || !$entityAttribute->getIsUserDefined()) {
            $this->buttonList->remove('delete');
        } else {
            $this->buttonList->update('delete', 'label', __('Delete Attribute'));
        }
    }
    /**
     * Get FormHtml
     *
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->setTemplate('Webkul_CustomRegistration::customfields/dependable.phtml')->toHtml();
        return $html;
    }

    /**
     * Get Model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }

    /**
     * _prepareLayout
     *
     * @return array
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            require([
                'jquery',
                'mage/mage',
                'knockout'
            ], function ($){
                $('#customfields_attribute_code').on('keyup',function(){
                   $(this).val($(this).val().replace(/\s+/g, '_'));
                });
                $('body').on('keyup','#customfields_dependable_inputname',function(){
                   $(this).val($(this).val().replace(/\s+/g, '_'));
                });
            });

        ";
        return parent::_prepareLayout();
    }

    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('customfields')->getId()) {
            $frontendLabel = $this->_coreRegistry->registry('customfields')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Customer Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Customer Attribute');
    }

     /**
      * Getter of url for "Save and Continue" button. tab_id will be replaced by desired by JS later
      *
      * @return string
      */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'customregistration/*/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'customregistration/*/save',
            ['_current' => true, 'back' => null, 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('customregistration/*/validate', ['_current' => true]);
    }

    /**
     * Get CustomFieldsData
     *
     * @return Object
     */
    public function getCustomFieldsData()
    {
        return $this->_coreRegistry->registry('customfields');
    }
}
