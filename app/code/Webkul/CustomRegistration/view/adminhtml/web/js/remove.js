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

    $('body').find('#customfields_'+$('.wkrm').attr('data')+'_hidden').attr('disabled','disabled');
    $('body').on('change', '.wkrm', function () {
        var wkrmValue = $(this).val();
        if (parseInt(wkrmValue)) {
            $('body').find('#customfields_'+$(this).attr('data')).attr('disabled','disabled');
            $('body').find('#customfields_'+$(this).attr('data')+'_hidden').removeAttr('disabled');
        } else {
            $('body').find('#customfields_'+$(this).attr('data')).removeAttr('disabled');
            $('body').find('#customfields_'+$(this).attr('data')+'_hidden').attr('disabled','disabled');
        }
    });

    $('body').on('change', 'input', function () {
        if ($(this).attr("type") == 'file') {
            var ext_arr = $(this).next().find('.data-extension').attr('data').split(",");
            var new_ext_arr = [];
            for (var i = 0; i < ext_arr.length; i++) {
                new_ext_arr.push(ext_arr[i]);
                new_ext_arr.push(ext_arr[i].toUpperCase());
            }
            if (new_ext_arr.indexOf($(this).val().split("\\").pop().split(".").pop()) < 0) {
                var self = $(this);
                self.val('');
                $('<div>').html('Invalid Extension. Allowed extensions are '+ $(this).next().find('.data-extension').attr('data'))
                .modal({
                    title: 'Attention!',
                    autoOpen: true,
                    buttons: [{
                    text: 'Ok',
                        attr: {
                            'data-action': 'cancel'
                        },
                        'class': 'action',
                        click: function () {
                            self.val('');
                            this.closeModal();
                        }
                    }]
                });
            }
        }
   });
});
