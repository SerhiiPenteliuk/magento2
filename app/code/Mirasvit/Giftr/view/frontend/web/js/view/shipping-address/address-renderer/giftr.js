define(
    [
        'ko',
        'underscore',
        'Mirasvit_Giftr/js/model/address-list',
        'Magento_Checkout/js/view/shipping-address/address-renderer/default',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
    ],
    function (
        ko,
        _,
        addressList,
        addressRenderer,
        customer,
        quote,
        checkoutData
    ) {
        'use strict';

        /**
         * Check if given address belongs to registrant or customer
         *
         * @return {boolean} true if address belongs to registrant otherwise false
         */
        function isAddressRegistrant(address) {
            var isRegistrantAddress = true;
             if (customer.customerData.addresses && customer.customerData.addresses.length) {
                 _.each(customer.customerData.addresses, function(customerAddress) {
                     if (customerAddress.customer_id == address.customerId) {
                        isRegistrantAddress = false;
                     }
                 });
             }

            return isRegistrantAddress;
        }

        return addressRenderer.extend({
            address: function() {
                return addressList();
            },

            initObservable: function() {
                this._super();

                if (isAddressRegistrant(this.address()) || (!customer.isLoggedIn() && this.address())) {
                    // Reset billing address when gift registrant address chosen as the shipping address
                    quote.billingAddress = ko.observable(null);
                    checkoutData.setSelectedShippingAddress(this.address().getKey());
                }

                return this;
            },

            selectAddress: function() {
                var isCustomerLoggedIn = customer.isLoggedIn();

                customer.setIsLoggedIn(isCustomerLoggedIn || true); // Temporary switch customer status to make it as loggedin ()

                this._super();

                customer.setIsLoggedIn(isCustomerLoggedIn); // Return initial customer status

                if (isAddressRegistrant(this.address()) || (!isCustomerLoggedIn && this.address())) {
                    // Reset billing address when gift registrant address chosen as the shipping address
                    // to prevent using gift registrant address as the billing address for order
                    quote.billingAddress = ko.observable(null);
                }
            }
        });
    }
);