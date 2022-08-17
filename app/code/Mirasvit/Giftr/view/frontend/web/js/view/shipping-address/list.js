define([
    'underscore',
    'Mirasvit_Giftr/js/model/address-list',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/view/shipping-address/list',
    'Magento_Checkout/js/model/shipping-save-processor',
    'Mirasvit_Giftr/js/model/shipping-save-processor/registrant-address'
], function(_, addressList, addressListDefault, list, shippingSaveProcessor, registrantAddressSaveProcessor) {
    'use strict';

    return list.extend({
        initialize: function () {
            this._super();

            // Add our own save processor for registrant shipping address
            shippingSaveProcessor.registerProcessor('registrant-address', registrantAddressSaveProcessor);

            return this;
        },

        defaults: {
            visible: !_.isEmpty(addressList()) || addressListDefault().length > 0,
            rendererTemplates: {
                'registrant-address': {
                    component: 'Mirasvit_Giftr/js/view/shipping-address/address-renderer/giftr'
                },
                'customer-address': {
                    component: 'Mirasvit_Giftr/js/view/shipping-address/address-renderer/giftr'
                }
            }
        },

        initChildren: function () {
            var addresses = addressListDefault();
            var registrantAddresses = addressList();
            if (registrantAddresses) {
                addresses.push(registrantAddresses);
            }

            _.each(addresses, this.createRendererComponent, this);

            return this;
        }
    });
});
