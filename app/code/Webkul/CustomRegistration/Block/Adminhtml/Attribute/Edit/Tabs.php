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

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * _construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('custom_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Information'));
    }

    /**
     * _beforeToHtml
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
  
        $this->addTab(
            'main',
            [
                'label' => __('Properties'),
                'title' => __('Properties'),
                'content' => $this->getChildHtml('main'),
                'active' => false
            ]
        );
        $this->addTab(
            'labels',
            [
                'label' => __('Manage Labels'),
                'title' => __('Manage Labels'),
                'content' => $this->getChildHtml('labels'),
                'active' => false

            ]
        );

        return parent::_beforeToHtml();
    }
}
