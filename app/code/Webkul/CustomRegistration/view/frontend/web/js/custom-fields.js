/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/alert",
    "mage/template",
    "mage/url",
    "mage/calendar",
    "jquery/validate"
], function ($, $t, alert, template, urlBuilder) {
    'use strict';
    var self;
    $.widget('mage.customFrontEndField', {
        _create: function () {
            self = this;
            $.each($('.dob_type'), function(i,v) {
                $(this).calendar({showsTime: false,dateFormat: "M/d/yy"});
            });
            $('.ui-datepicker-trigger').show();
            $('.ui-datepicker-trigger').on('click', function() {
                var id = $(this).val();
                $("#"+id).focus()
                $("#"+id ).trigger( "click" );
            });

            $.validator.addMethod(
                "wk-date-validator",
                function(value, element) {
                    var test = new Date(value);
                    var re = /^\d{1,2}\/\d{1,2}\/\d{0,4}$/;
                    let res = $.mage.isEmptyNoTrim(value) || !isNaN(test) && re.test(value);
                    return res;
                },
                $.mage.__("Please enter a valid date.")
            );
            $('.customfields, .additional_info, .wk-edit-additional-info').on('change', '.dependent_fields', function() {
                self.getDependentField($(this));
            });
            $('.wk-edit-additional-info .dependent_fields').trigger("change");
            $('.additional_info .dependent_fields').trigger("change");

            $('.form-edit-account').on('click', '.wk-del-file-icon', function(e) {
                e.preventDefault();
                $(this).parents('.control').find('.input-file.custom_file').val('');
                $(this).parents('.control').find('input[type=hidden]').val('');
                $(this).parents('.control').find('a.wk_file').remove();
                $(this).remove();
            });
            $('.form-edit-account').on('click', '.wk-del-icon', function(e) {
                e.preventDefault();
                $(this).parents('.wk_images').next('.input-file.custom_file').val('');
                $(this).parents('.wk_images').next('.input-file.custom_file').next('input[type=hidden]').val('');
                $(this).parents('.wk_images').remove();
            });
            /* End of Dependable Field controll*/
            $('.additional_info, .form-edit-account, .form-create-account').on('change', '.custom_file', function() {
                if ($(this).hasClass('wk_attr_custom_image')) {
                    let this_input = $(this);
                    for (var i=0; i<this_input[0].files.length; i++) {
                        if (this_input[0].files && this_input[0].files[i]) {
                            let this_file = this_input[0].files[i];
                            if (!this_file.type.match('image.*')) {
                                alert({
                                    content: $t("'%1' is not a valid file.").replace('%1', this_file.name)
                                });
                                this_input.val('');
                            }
                        }
                    }
                }
                var ext_arr = $(this).attr("data-allowed").split(",");
                if ($(this).val() && ext_arr.indexOf($(this).val().split("\\").pop().split(".").pop()) < 0) {
                    alert({
                        content: $t("Invalid File Extension. Allowed extensions are %1")
                            .replace('%1', $(this).attr("data-allowed"))
                    });
                    $(this).val('');
                }

                self.checkSize($(this));
            });

            $('.additional_info, .form-edit-account').on('change', '.attribute_image', function() {
                self.halfUpload($(this));
            });
            $('.additional_info, .form-edit-account').on('change', '.wkinput-switch', function() {
                self.wkChangeValueCheckbox(this, $(this).attr('data-parent'));
            })
        },
        wkChangeValueCheckbox: function (element, parent) {
            if (element.checked) {
                document.getElementById(parent).value = 1;
            } else {
                document.getElementById(parent).value = 0;
            }
        },
        halfUpload: function (this_input) {
            $("#wk_add_images_container").html("");
            for (var i=0; i<this_input[0].files.length; i++) {
                if (this_input[0].files && this_input[0].files[i]) {
                    let this_file = this_input[0].files[i];
                    if (this_file.type.match('image.*')) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $(this_input).siblings("#wk_add_images_container").html('');
                            $(this_input).siblings("#wk_add_images_container")
                            .append("<span class='wk_rma_image_cover'><img class='wk_rma_image' src='"
                            +e.target.result+"' width='75' height='75'/></span>");
                        }
                        reader.readAsDataURL(this_input[0].files[i]);
                    } else {
                        alert({
                            content: $t("'%1' is not a valid file.").replace('%1', this_file.name)
                        });
                        this_input.val('');
                    }
                }
            }
        },
        checkSize: function (thisInput) {
            for (var i=0; i<thisInput[0].files.length; i++) {
                if (thisInput[0].files && thisInput[0].files[i]) {
                    let thisFile = thisInput[0].files[i];
                    let fsize = thisFile.size;
                    if (fsize > 2000000) {
                        alert({
                            content: $t("File you are trying to upload exceeds maximum file size limit.")
                        });
                        thisInput.val('');
                    }
                }
            }
        },
        updateChildFields: function(){
            var dependableIds = $(document).find('.additional_info').find('.customfields').find('div[class*="dependable-"]');
            var childAttr = [];
            var checkIfParentPresent = [];
            if(dependableIds.length > 0){
            $.each($(dependableIds), function(i) {
                childAttr[i] = dependableIds[i].classList[2].substr(dependableIds[i].classList[2].indexOf('-')+1,dependableIds[i].classList[2].length);
            })
            var x = $(document).find('.additional_info').find('.customfields').find('.field').find('.control').find('[class*="dependable_child_"], .dependent_fields');
            for(var i=0; i < x.length; i++){
                checkIfParentPresent[i] = $(x[i]).attr('data-attr-id');
            }
                var difference = $(childAttr).not(checkIfParentPresent).get();
                $.each($(difference), function(i){
                    $(document).find('.dependable-'+difference[i]).remove();
                })
        }
        var dependableIds2 = $(document).find('.wk-edit-additional-info').find('div[class*="dependable-"]');
            var childAttr2 = [];
            var checkIfParentPresent2 = [];
            if(dependableIds2.length > 0){
            $.each($(dependableIds2), function(i) {
                childAttr2[i] = dependableIds2[i].classList[2].substr(dependableIds2[i].classList[2].indexOf('-')+1,dependableIds2[i].classList[2].length);
            })
            var x = $(document).find('.wk-edit-additional-info').find('.field').find('.control').find('[class*="dependable_child_"], .dependent_fields');
            for(var i=0; i < x.length; i++){
                checkIfParentPresent2[i] = $(x[i]).attr('data-attr-id');
            }
                var difference2 = $(childAttr2).not(checkIfParentPresent2).get();
                $.each($(difference2), function(i){
                    $(document).find('.dependable-'+difference2[i]).remove();
                })
        }
        },
        getDependentField: function (thisval) {
            window.radioSelected = undefined;
            var parentField = thisval;
            if($(thisval).attr('type') == 'radio'){
                var radioName = $(thisval).attr('name');
                window.radioSelected = $(`input[name=${radioName}]:checked`).val();
            }
            $.ajax({
                url: self.options.dependentFieldUrl,
                data: {
                    form_key: window.FORM_KEY,
                    attr_id:thisval.attr('data-attr-id'),
                    opt_id: window.radioSelected??thisval.val(),
                    customerId: self.options.customerData.entity_id
                },
                type: 'POST',
                dataType:'JSON',
                showLoader: true,
                success: function(dependentFields){
                    var dependentClass = 'dependable-'+parentField.attr('data-attr-id');
                    var element = $(document).find('.'+dependentClass);
                    $('.'+dependentClass).remove();
                    self.updateChildFields();
                    if (dependentFields.totalRecords) {
                        $(dependentFields.fields).each(function (index, attribute) {
                            var dependentField = template('#dependent_field_template');
                            let value = attribute.value;
                            var field = dependentField({
                                data: {
                                    dependableClass: dependentClass,
                                    fieldDetails: attribute,
                                    value: value,
                                    fileUrl: value != '' ? attribute.filePath : ''
                                }
                            })
                            parentField.parents('.field').after(field);
                            var subChildren = $('.'+dependentClass).find('.control').find('.dependent_fields');
                            if(subChildren.length){
                            subChildren.each(function (j){
                                $(subChildren[j]).trigger('change');
                            })
                        }
                            $('.'+dependentClass).find('.dob_type').calendar({showsTime: false,dateFormat: "M/d/yy"});
                        });
                    }
                },
                error:function(error){
                    $('<div>').html(error)
                        .modal({
                            title: $.mage.__('Attention'),
                            autoOpen: true,
                            buttons: [{
                                text: 'OK',
                                attr: {
                                    'data-action': 'cancel'
                                },
                                'class': 'action-primary',
                                click: function() {
                                    this.closeModal();
                                }
                            }]
                        });
                }
            });
        }
    });
    return $.mage.customFrontEndField;
});
