define([
    'jquery',
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function($, Component, _, customerData, $t) {
    'use strict';

    var login = 'login';

    return Component.extend({
        defaults: {
            hasRegistries: false,
            url: null,
            registries: [],
            selected: [],
            loginUrl: null,
            newRegistryUrl: null
        },

        initialize: function () {
            this._super();
            this.initRegistries();
        },

        initRegistries: function() {
            var customerRegistries = customerData.get('gift-registry')();

            this.isLoggedIn(customerRegistries.is_logged_in || false);
            this.registries(customerRegistries.registries || []);
            this.selected(customerRegistries.selected || []);
            this.hasRegistries(this.registries().length > 0);
        },

        initObservable: function() {
            this._super()
                .observe([
                    'selected',
                    'registries',
                    'hasRegistries',
                    'isLoggedIn'
                ]);

            return this;
        },

        defineBehaviour: function(data, event) {
            this.initRegistries();

            if (this.registries().length == 1) {
                event.stopPropagation();
                this.addProduct();
            }
        },

        getData: function() {
            var data = $('#product_addtocart_form').serializeArray();
            if (_.size(this.selected()) > 0) {
                data.push({name: 'registries', value: _.map(this.selected(), function(value) { return value })});
            }

            return data;
        },

        addProduct: function() {
            if (!$('#product_addtocart_form').valid()) {
                return false;
            }

            $.ajax({
                url: this.url,
                method: 'POST',
                data: this.getData(),
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    var giftr = $('[data-block="addtogiftr"]');
                    giftr.find('[data-role="dropdownDialog"]').dropdownDialog("close");
                    $('.giftr-dropdown').hide();
                    if (response.status == this.login) {
                        setLocation(response.message);
                    }
                }
            });
        },

        /**
         * Should message be shown in gift registry dropdown or not.
         */
        isMessageVisible: function() {
            return !this.hasRegistries() || !this.isLoggedIn();
        },

        /**
         * Retrieve message for gift registry dropdown.
         */
        getMessage: function() {
            var message = '';

            if (!this.isLoggedIn()) {
                message = $t('Please') + ', <a href="' + this.loginUrl + '">'+ $t('log in') +
                    '</a> ' + $t('before adding products to registry')
            } else if (!this.hasRegistries()) {
                message = $t('You have no Gift Registries yet.') +
                    ' <a href="' + this.newRegistryUrl + '">' + $t('Create Gift Registry') + '</a>';
            }

            return message;
        }

    });
});
