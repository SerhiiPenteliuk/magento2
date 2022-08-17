define([
    'underscore',
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal',
    'uiRegistry',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function(_, $, Component, ko, confirm, modal, registry, customerData) {
    'use strict';

    var SUCCESS   = 'success';
    var CONFIRM   = 'confirm';
    var ERROR     = 'error';
    var LOGIN     = 'login';

    var CO_REGISTRANT_BTN_TEXT_ADD    = $.mage.__('Add Co-Registrant');
    var CO_REGISTRANT_BTN_TEXT_REMOVE = $.mage.__('Remove Co-Registrant');
    var ITEM_BLOCK_SELECTOR_PREFIX    = '#item_';

    var popUp = null;

    ko.bindingHandlers.fadeVisible = {
        init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            var visible = bindingContext.$data.coRegistrantExists();
            $(element).toggle(visible);
        },
        update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
            var visible = bindingContext.$data.coRegistrantExists();
            visible ? $(element).slideDown() : $(element).slideUp();
        }
    };

    return Component.extend({
        defaults: {
            chosenTypeId: null,
            coRegistrantExists: 0,
            coRegistrantBtnText: CO_REGISTRANT_BTN_TEXT_ADD,
            shareUrlFb: null,
            shareUrlGoogle: null,
            shareUrlTwitter: null,
            removeItemUrl: null,
            addToCartUrl: null
        },

        initialize: function() {
            _.bindAll(this, 'saveNewAddress');
            this._super();

            return this;
        },

        initObservable: function() {
            this._super()
                .observe('chosenTypeId')
                .observe('coRegistrantExists')
                .observe('coRegistrantBtnText');

            return this;
        },

        toggleCoRegistrant: function() {
            if (!this.coRegistrantExists()) {
                this.coRegistrantExists(1);
                this.coRegistrantBtnText(CO_REGISTRANT_BTN_TEXT_REMOVE);
            } else {
                this.coRegistrantExists(0);
                this.coRegistrantBtnText(CO_REGISTRANT_BTN_TEXT_ADD);
            }
        },

        removeItem: function(itemId) {
            var self = this;
            confirm({
                content: $.mage.__('Are you sure you want to remove this product from your Gift Registry?'),
                actions: {
                    confirm: function () {
                        var promise = self.sendRequest(self.removeItemUrl, {item_id: itemId});
                        promise.success(function(response) {
                            if (response.status == SUCCESS) {
                                $(ITEM_BLOCK_SELECTOR_PREFIX + itemId).fadeOut('slow');
                            }
                        });
                    }
                }
            });
        },

        deleteRegistry: function(itemId) {
            this.sendRequest('delete', {'id' :itemId});
            customerData.invalidate();
            customerData.reload('gift-registry');
            document.location.reload();
        },

        addToCart: function(itemId) {
            var qty = $(ITEM_BLOCK_SELECTOR_PREFIX + 'qty_' + itemId).val();
            this.sendRequest(this.addToCartUrl, {item_id: itemId, qty: qty});
        },

        sendRequest: function(url, data) {
            return $.ajax(url, {
                method: 'POST',
                data: data,
                dataType: 'json',
                showLoader: true
            });
        },

        win: function(data, event) {
            var url = '';
            var w = 600;
            var h = 430;
            var windowW = w;
            var windowH = h;
            var windowX = (screen.width / 2) - (windowW / 2);
            var windowY = (screen.height / 2) - (windowH / 2);

            if ($(event.target).hasClass('share-fb')) {
                url = this.shareUrlFb;
            } else if ($(event.target).hasClass('share-google')) {
                url = this.shareUrlGoogle;
            } else if ($(event.target).hasClass('share-twitter')) {
                url = this.shareUrlTwitter;
            }

            window.open(url, 'Share Gift Registry', 'width=' + w + ',height=' + h + ',top=' + windowY + ',left=' + windowX + ',resizable=yes,scrollbars=yes,addressbar=no,status=no,menubar=no,toolbar=no');
        },

        /**
         * Show popUp form
         */
        showFormPopUp: function() {
            this.getPopUp().openModal();
        },

        /**
         * Create modal dialog
         *
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
                        click: function () {
                            this.closeModal();
                        }
                    }
                ];

                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var self = this,
                addressData;

            if (!_.isFunction(this.source)) {
                this.source = registry.get(this.provider);
            }

            this.source.set('params.invalid', false);
            this.source.trigger('shippingAddress.data.validate');

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                // if user clicked the checkbox, its value is true or false. Need to convert.
                this.sendRequest(this.popUpForm.options.saveAddressUrl, addressData)
                    .done(function(result) {
                        if (result.success) {
                            $(self.popUpForm.options.shippingSelectId).append($('<option>', {
                                value: result.value,
                                text: result.label
                            })).val(result.value);
                        }
                    });

                this.getPopUp().closeModal();
            }
        }
    });
});