define([
    'jquery',
    'jquery/ui',
    'ko',
    'Magento_Ui/js/modal/modal',
    'underscore'
], function($, ui, ko, modal, _) {
    'use strict';

    $.widget('mirasvit.GiftrNewItem', {
        options: {
            url: null,
            formId: null
        },

        attributes: _.toArray($.attributes),

        /**
         * Method called automatically and initializes jQuery widget
         */
        _create: function() {
            this.element
                .off('click.button')
                .on('click.button', $.proxy(this.show, this));

            this._super();

            _.bindAll(this, 'save');
        },

        /**
         * Method used to show modal window with new registry item form
         */
        show: function() {
            var self = this;

            this.modal = $('<div/>').modal({
                type: 'slide',
                title: $.mage.__('Add Product'),
                modalClass: 'registry-new-item-aside',
                closeOnEscape: true,
                opened: function() {
                    self.update();
                },
                closed: function() {
                    $('.registry-new-item-aside').remove();
                },

                buttons: []
            });

            this.modal.modal('openModal');
        },

        /**
         * Method updates modal window view
         */
        update: function() {
            var self = this;

            $('body').trigger('processStart');

            $.ajax({
                method: 'GET',
                url: this.options.url,
                data: {}
            }).done(function(html) {
                $(self.modal).html(html);

                ko.applyBindings(self, $(self.modal)[0]);

                $('body').trigger('processStop');
            })
        },

        /**
         * Method saves new item
         */
        save: function() {
            var formEl = $('#' + this.options.formId);

            formEl.validate({
                ignore: ".skip-submit",
                errorClass: "mage-errro"
            });

            var validationResult = formEl.valid();
            if (validationResult) {
                $('body').trigger('processStart');

                $.ajax({
                    method: 'GET',
                    url: formEl.attr('action'),
                    data: formEl.serialize()
                }).done(function(response) {
                    $('body').trigger('processStop');
                    if (response.error) {
                        throw response;
                    } else if (response.ajaxRedirect) {
                        setLocation(response.ajaxRedirect);
                    }
                });
            }
        }
    });

    return $.mirasvit.GiftrNewItem;
});