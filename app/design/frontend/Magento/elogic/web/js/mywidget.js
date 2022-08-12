define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('elogic.mywidget', {
        /**
         * @private
         */
        _create: function (addToCart) {
            $('#minusQty').click(function () {
                if ($('#qty').val() > 1) {
                    $('#qty').val(parseInt($('#qty').val()) - 1);
                }
                else {
                    $('#qty').val(1)
                }
            });

            $('#addQty').click(function () {
                $('#qty').val(parseInt($('#qty').val()) + 1);
            });
            return addToCart;
        }
    });

    return $.elogic.mywidget;
});
