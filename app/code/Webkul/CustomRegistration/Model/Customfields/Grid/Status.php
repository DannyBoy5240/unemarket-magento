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

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Webkul\CustomRegistration\Model\Customfields
     */
    protected $fields;
    /**
     * Constructor.
     *
     * @param \Webkul\CustomRegistration\Model\Customfields $fields
     * @return \Webkul\CustomRegistration\Model\Customfields
     */
    public function __construct(\Webkul\CustomRegistration\Model\Customfields $fields)
    {
        $this->fields = $fields;
    }
    /**
     * To Option Array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->fields->getStatusOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                    'label' => $value,
                    'value' => $key,
                ];
        }

        return $options;
    }
}
