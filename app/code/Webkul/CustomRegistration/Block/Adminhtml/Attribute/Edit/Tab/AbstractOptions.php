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

abstract class AbstractOptions extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Preparing layout, adding buttons
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild('options', \Webkul\CustomRegistration\Block\Adminhtml\Attribute\Edit\Options::class);
        return parent::_prepareLayout();
    }

    /**
     * _toHtml
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getChildHtml();
    }
}
