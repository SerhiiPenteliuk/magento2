define([
        'jquery',
        'uiComponent',
        'underscore',
        'Magento_Customer/js/customer-data',
        'mage/translate'
    ], function ($, Component, _, customerData, $t) {
        'use strict';

        var mixin = {
            defineBehaviour: function () {
                if (!this.isLoggedIn()) {
                    $('#addto-giftr').click(window.location.assign(this.loginUrl));
                }
                if (this.registries._latestValue.length === 1) {
                    event.stopPropagation();
                    this.addProduct();
                }
            },
        };

        return function (target) {
            return target.extend(mixin);
        };

    }
);
