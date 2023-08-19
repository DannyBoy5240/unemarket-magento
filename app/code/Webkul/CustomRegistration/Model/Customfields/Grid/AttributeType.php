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
namespace Webkul\CustomRegistration\Model\Customfields\Grid;

class AttributeType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'text','label' => __('Text')],
            ['value' => 'textarea','label' => __('Text Area')],
            ['value' => 'dob','label' => __('Date')],
            ['value' => 'select','label' => __('Dropdown')],
            ['value' => 'multiselect','label' => __('Multiple Select')],
            ['value' => 'radio','label' => __('Radio Button')],
            ['value' => 'image','label' => __('Media Image')],
            ['value' => 'file','label' => __('File')],
            ['value' => 'dependable','label' => __('Dependable Field')],
        ];

        return $options;
    }
}
