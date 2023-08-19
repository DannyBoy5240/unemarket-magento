define('js/theme', [
    'jquery',
    'domReady!'
], function ($) {
    'use strict';
    $(document).ready(function () { 
        //For reload of page and admin order edit page
        setTimeout(dependableFields,1000);
        
        //For new order create page
        $(document).on('click', "#sales_order_create_customer_grid_table > tbody", function () {
            setTimeout(dependableFields,1000);
        });
    });

    function updateFields(parent,children, attrValue)
    {
        children.each(function(){
            let childId = $(this).attr('id');
            let childContainer = $(this).parents(`div[class*='${childId}']`);
            $(parent).after(childContainer);
            let childClasses = $(this).attr('class').split(' ');
            childClasses.forEach(element => {
                if (element.indexOf('dependable_child')>-1)
                {
                    if(element.split('_').pop()==attrValue){
                        childContainer.show();
                    }
                    else{
                        childContainer.hide();
                    }
                }
            });
        });
    }

    function dependableFields()
    {
        //hiding all child fields 
        $("*[class*='child_']").each(
            function() {
                var id = $(this).attr('id');
                var parent = $(this).parents(`div[class*='${id}']`);
                parent.hide();
        });

        $('.order-account-information, select[class*="dependable_field_"]').on('change', "select[class*='dependable_field_']", function () {
            var id = $(this).attr('id');
            var parent = $(this).parents(`div[class*='${id}']`);
            var attrValue = $.trim($(this).find("option:selected").val());
            var children = $(`*[class*='child_${id}']`);
            updateFields(parent,children, attrValue);
        });

        $('.order-account-information, select[class*="dependable_field_"]').trigger('change');
    }
});