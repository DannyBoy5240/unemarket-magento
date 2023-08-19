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
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($) {
    'use strict';

    return function (optionsConfig) {
        var conditionModel = $('<div>').html(optionsConfig.condition).modal({
            modalClass: 'term-condition ' + optionsConfig.animate,
            type: optionsConfig.animate,
            title: optionsConfig.termheading,
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: optionsConfig.buttontitle,
                'class': 'primary',
                click: function () {
                    this.closeModal();
                }
            }]
        });
        var privacyModel = $('<div>').html(optionsConfig.privacy).modal({
            modalClass: 'privacy-condition ' + optionsConfig.animate,
            type: optionsConfig.animate,
            title: optionsConfig.privacyheading,
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: optionsConfig.buttontitle,
                'class': 'primary',
                click: function () {
                    this.closeModal();
                }
            }]
        });
        $('#read-term-condition').on('click', function (event) {
            event.preventDefault();
            conditionModel.modal('openModal');
        });
        $('#read-privacy').on('click', function (event) {
            event.preventDefault();
            privacyModel.modal('openModal');
        });
    };
});
