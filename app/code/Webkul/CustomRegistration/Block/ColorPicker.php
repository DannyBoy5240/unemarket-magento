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
namespace Webkul\CustomRegistration\Block;

class ColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Add color picker in admin configuration fields
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $el = $("#' . $this->escapeHtml($element->getHtmlId()) . '");
                    $el.css("backgroundColor", "'. $value .'");

                    $el.ColorPicker({
                        color: "'. $this->escapeHtml($value) .'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
            </script>';

        return $html;
    }
}
