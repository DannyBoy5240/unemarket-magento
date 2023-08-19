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
namespace Webkul\CustomRegistration\Model\Customer\Attribute\Backend;

class Multiselect extends \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Webkul\CustomRegistration\Model\CustomfieldsFactory
     */
    protected $_customField;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\CustomRegistration\Model\Customfields $customfields
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\CustomRegistration\Model\Customfields $customfields,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->_customField = $customfields;
    }

    /**
     * Before Attribute Save Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();

        $customFieldCollection = $this->_customField->getCollection();
        $customFieldCollection->addFieldToFilter('attribute_code', $attributeCode);
        if ($customFieldCollection->getSize() > 0) {
            if ($this->getAttribute()->getFrontendInput() == 'multiselect') {
                $data = $this->request->getPostValue();
                if (isset($data[$attributeCode]) && !is_array($data[$attributeCode])) {
                    $data = [];
                }
                if (isset($data[$attributeCode])) {
                    $object->setData($attributeCode, join(',', $data[$attributeCode]));
                }
            }
            if (!$object->hasData($attributeCode)) {
                $object->setData($attributeCode, false);
            }
            return $this;
        }

        return parent::beforeSave($object);
    }

    /**
     * After Load Attribute Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($this->getAttribute()->getFrontendInput() == 'multiselect'
        && $this->getAttribute()->getAttributeModel() == \Magento\Customer\Model\Attribute::class) {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $object->setData($attributeCode, explode(',', $data));
            } else {
                $object->setData($attributeCode, []);
            }
        }
        return $this;
    }
}
