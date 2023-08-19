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

use Magento\Store\Model\ResourceModel\Store\Collection;

class Options extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_CustomRegistration::customfields/options.phtml';

    /**
     * Retrieve attribute object from registry
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected function getAttributeObject()
    {
        $parentAttribute = $this->_registry->registry('entity_attribute');
        if ($parentAttribute->getFrontendInput() == 'dependable') {
            return $this->_registry->registry('dependfields');
        } else {
            return $this->_registry->registry('entity_attribute');
        }
    }

    /**
     * Retrieve attribute default value
     *
     * @return int|null
     */
    public function getAttributeDefaultValue()
    {
        return $this->getAttributeObject()->getDefaultValue();
    }
}
