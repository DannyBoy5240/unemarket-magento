/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define('js/theme', [
    'jquery',
    'domReady!'
], function ($) {
    'use strict';
    $(document).ready(function () {

        $('#manage-titles-wrapper').hide();
       
        $('#custom_attribute_tabs_labels').click(function(){
            $('#manage-titles-wrapper').show();
            $('#customfields_base_fieldset-wrapper').hide();
            $('#manage-options-panel').hide();
            $('.legend').hide();
            $('#dependable_advanced_fieldset-wrapper').hide();
        });
        $('#custom_attribute_tabs_main').click(function(){
            $('#customfields_base_fieldset-wrapper').show();
            $('#manage-options-panel').show();
            $('.legend').show();
            $('#dependable_advanced_fieldset-wrapper').show();
            $('#manage-titles-wrapper').hide();
        });
        
        $(document).on('click', "#tab_custom_registration", function () {
            // $(document).ready(function(){
            setTimeout(function () {
            // $(document).find('#tab_custom_registration').trigger('click'); 
            // },100);
                $("div[class*='dependable_field_'] select").each(function() {
                    var parent = $(this).parents("div[class*='dependable_field_']");
                        var parentDataIndex = parent.data('index');
                        var children = $(`div[class*='child_${parentDataIndex}']`);
                        $(parent).after(children);
                        children.hide();
                        var attrValue = $.trim($(this).find("option:selected").val());
                        updateFields(children, attrValue);
                        updateAllChildFields();
                });
                
                $(document).on('change', "div[class*='dependable_field_'] select", function () {
                    var parentDataIndex = $(this).parents("div[class*='dependable_field_']").data('index');
                    var children = $(`div[class*='child_${parentDataIndex}']`);
                    children.trigger('change');
                    children.hide();
                    var attrValue = $.trim($(this).find("option:selected").val());
                    updateFields(children, attrValue);
                    children.trigger('change');
                    updateAllChildFields();
                });
                $(document).on('change', "div[class*='dependable_child_'] select", function () {
                    var parentDataIndex = $(this).parents("div[class*='dependable_child_']").data('index');
                    var children = $(`div[class*='child_${parentDataIndex}']`);
                    children.hide();
                    children.trigger('change');
                    var attrValue = $.trim($(this).find("option:selected").val());
                    updateFields(children, attrValue);
                });
            }, 2000);
        });

    });

    function updateFields(children, attrValue)
    {
        children.each(function(){
            let childClass = $(this).attr('class');
            if(childClass.indexOf(attrValue)>-1){
                $(this).show();
                $(this).find('select').trigger('change');
            }
        });
    }
    function updateAllChildFields(){
        var allChildFields = $(document).find("div[class*='dependable_child_']");
        allChildFields.each(function (i){
            $(allChildFields[i]).find('select').trigger('change');
            if(allChildFields[i].style.display == "none"){
                $(document).find(`div[class*='dependable_child_${$(allChildFields[i]).data('index')}']`).each(function(){
                    $(this).hide();
                })
            }
        })
    }
});