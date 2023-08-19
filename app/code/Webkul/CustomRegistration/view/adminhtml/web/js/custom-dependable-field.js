/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
        "jquery",
        'mage/translate',
        "mage/template",
        "mage/mage",
        "mage/calendar",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.customDependableField', {

            options: {
                optionTemp : '',
                dependTemp : '',
                allowedExtensionTmp : '#allowed_extension_template',
                dependableExtnTemp: '#dependable_allowed_extension_template',
                tabMainContent : '#custom_attribute_tabs_main_content',
                customFiledOption : '.customfield_options',
                dependableWrapper : '#dependable_advanced_fieldset-wrapper',
                baseFieldSelector : '#customfields_base_fieldset',
                frontEndClass : '.field-frontend_class',
                isRequired : '.field-is_required',
                frontEndInput: '#customfields_frontend_input',
                hasParentElement: '#customfields_has_parent',
                dependableAttrCode: '#dependable_attribute_code',
                dependInput : '#dependable_attribute_opt',
            },
            _create: function () {
                var self = this;
                this.options.dependTemp = $(self.options.tabMainContent).find(self.options.dependableWrapper).parent('.entry-edit');
                this.options.optionTemp = $(self.options.tabMainContent).find(self.options.customFiledOption);
                $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                $(self.options.tabMainContent).find(self.options.dependableWrapper).parent('.entry-edit').remove();

                if (self.options.codeSignal == 1) {
                    $(self.options.baseFieldSelector).find("#customfields_attribute_code").attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.frontEndInput).attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                }

                if (self.options.hasParent == 1) {
                    $(self.options.tabMainContent).append(this.options.dependTemp);
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(self.options.baseFieldSelector).find(".field-is_required").hide();

                }
                if (self.options.fileSignal == "1") {
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                    var progressTmpl = mageTemplate(self.options.allowedExtensionTmp),
                                  tmpl;
                        tmpl = progressTmpl({
                                data: {
                                    allowedextension: self.options.fileExtensionValue,
                                    attrType: self.options.fileType
                                }
                            });
                        $('.field-frontend_input').after(tmpl);
                }
                if (self.options.selectSignal == "1") {
                    $(self.options.tabMainContent).find(self.options.customFiledOption).show();
                    $(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if (self.options.textareaSignal == "1") {
                    $(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if (self.options.booleanSignal == "1") {
                    $(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.isRequired).hide();
                }
                $(self.options.frontEndInput).on('change',function () {
                    self._manageFields($(this));
                });
                $(self.options.hasParentElement).on('change',function () {
                    self._manageDependentField($(this));
                });
                /* if dependable templates have some data then add template automatically */
                if (self.options.dependableTextareaSignal == "1") {
                    $(self.options.baseFieldSelector).find("#customfields_dependable_validation_type").attr('disabled','true');
                }
                if (self.options.dependableBoolean == "1") {
                    $(self.options.baseFieldSelector).find("#customfields_dependable_validation_type").attr('disabled','true');
                    $(self.options.tabMainContent).find(".field-is_required").hide();
                }
                if (self.options.dependableSelectoptionSignal == "1") {
                    $(self.options.tabMainContent).find(self.options.dependableWrapper).append(this.options.optionTemp);
                    this.options.optionTemp.show();
                    $("#custom_attribute_tabs_main_content").find("#dependable_frontend_class").attr('disabled','true');
                }
                if (self.options.textSignal != "1") {
                  $("#custom_attribute_tabs_main_content").find("#dependable_frontend_class").attr('disabled','true');
                }
                if (self.options.dependableAllowedAxtensionsSignal == "1") {
                    var progressTmpl = mageTemplate(self.options.dependableExtnTemp),
                                  tmpl;
                        tmpl = progressTmpl({
                                data: {
                                    dependExtension:self.options.dependableExtnValue,
                                    attrType: self.options.fileType
                                }
                            });
                        $('#dependable_frontend_input').parent().parent().after(tmpl);
                        $('#dependable_inputtype').after(tmpl);
                }
                $(self.options.tabMainContent).delegate(self.options.dependableAttrCode,'change',function () {
                    self.dependableAttrChange($(this));
                });
            },
            _manageFields: function (thisval) {
                var self = this;
                $(thisval).parents(self.options.baseFieldSelector).find(".dependable_type_container").remove();
                $(thisval).parents(self.options.baseFieldSelector).find(".selectoption_type_container").remove();
                self.options.optionTemp.remove();
                $(thisval).parents(self.options.tabMainContent).find(".allowed_extensions_type_container").remove();
                if ($(thisval).val() == 'text') {
                    $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").removeAttr('disabled');
                }
                if ($(thisval).val() == 'textarea') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if ($(thisval).val() == 'boolean') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").hide();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                 if ($(thisval).val() == 'date') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if ($(thisval).val() == 'select' || $(thisval).val() == 'multiselect' || $(thisval).val() == 'radio' ) {
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                    $(self.options.tabMainContent).find("#customfields_base_fieldset-wrapper").append(self.options.optionTemp);
                    self.options.optionTemp.show();
                }
                if ($(thisval).val() == 'file' || $(thisval).val() == 'image') {
                    let attrType = 'image';
                    var extn = 'jpg,jpeg,png,gif';
                    if ($(thisval).val() == 'file') {
                        extn = 'pdf,zip,doc';
                        attrType = 'file';
                    }
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                    var progressTmpl = mageTemplate(self.options.allowedExtensionTmp),
                              tmpl;
                    tmpl = progressTmpl({
                            data: {allowedextension: extn, attrType:attrType}
                        });
                    $('.field-frontend_input').after(tmpl);
                }
            },
            _manageDependentField: function (thisval) {
                var self = this;
                if (thisval.val() == 1) {
                    $(self.options.tabMainContent).append(this.options.dependTemp);
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").hide();
                } else {
                    $(self.options.tabMainContent).find(self.options.dependableWrapper).parent('.entry-edit').remove();
                }
            },
            dependableAttrChange: function (thisval) {
                var self = this;
                $.ajax({
                    url: self.options.attributeOptionsUrl,
                    data: {form_key: window.FORM_KEY,attr_id:thisval.val()},
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function(attrOpt){
                        var select= $('#dependable_attribute_opt');
                        select.html($('<option>').text($.mage.__('Select Option')));
                        if (attrOpt.totalRecords) {
                            $(attrOpt.options).each(function(i,opt){
                                select.append($('<option>').val(opt.value).text(opt.label));
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
                        subcat.next('img').remove();
                    }
                });
            }
        });
    return $.mage.customDependableField;
    });
