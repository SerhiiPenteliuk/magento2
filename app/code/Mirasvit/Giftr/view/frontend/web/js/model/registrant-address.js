define(
    [
        'Magento_Customer/js/model/customer/address'
    ],
    function(address) {
        "use strict";

        return {
            getRegistrantAddress: function() {
                var item;
                var registrantData = window.giftRegistrantData;
                if (Object.keys(registrantData).length) {
                    item = new address(registrantData);
                    // Do not allow using gift registrant address as the billing address for order
                    item.canUseForBilling = function() {
                        return false;
                    };
                    item.isAddressSameAsShipping = function() {
                        return false;
                    };
                    item.isPlaceOrderActionAllowed = function() {
                        return false;
                    };
                    item.getType = function() {
                        return 'registrant-address';
                    }
                }

                return item;
            }
        }
    }
);