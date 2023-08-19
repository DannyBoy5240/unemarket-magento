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
namespace Webkul\CustomRegistration\Model\Config\Source;

class Content implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Agreements list getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $content = [
            [
                'value'=> 0,
                'label'=> __('Text')
            ],
            [
                'value'=> 1,
                'label'=> __('HTML')
            ]
        ];
        return $content;
    }
}
