define([
    'jquery',
], function ($) {
    'use strict';

    $.widget('mage.awSarp2SubscriptionDropdownShowHide', {
        options: {
            widgetSelector: '[data-role="aw-sarp2-subscription-type"]',
            dropDownSelector: '.aw-sarp2-subscription__options-list',
            detailsSelector: '[data-role="aw-sarp2-subscription-details"]'
        },

        /**
         * Subscription options list jquery element
         */
        optionsWidget: undefined,

        /**
         * Subscription options select jquery element
         */
        dropdown: undefined,

        /**
         * Subscriptions details block
         */
        details: undefined,

        /**
         * @inheritdoc
         * */
        _create: function () {
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            this.optionsWidget = $(this.options.widgetSelector);
            this.dropdown = this.optionsWidget.find(this.options.dropDownSelector);
            this.details = this.optionsWidget.find(this.options.detailsSelector);

            this._hide();

            this.element.trigger('awSarp2SubscriptionDropdownShowHide.initialized');
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                'change input': 'onChange'
            });
        },

        /**
         * On change value event handler
         */
        onChange: function (event) {
            var value = $(event.currentTarget).val();

            if (value == 0) {
                this._hide();
            } else {
                this._show();
                this.dropdown.trigger('change');
            }
        },

        /**
         * Hide elements
         *
         * @private
         */
        _hide: function () {
            if (this.optionsWidget.awSarp2SubscriptionOptionList) {
                this.optionsWidget.awSarp2SubscriptionOptionList('setValue', 0);
            }
            this.dropdown.hide();
            this.dropdown.prop('disabled', true);
            this.details.hide();
        },

        /**
         * Show elements
         *
         * @private
         */
        _show: function () {
            this.dropdown.show();
            this.dropdown.prop('disabled', false);
            this.details.show();
        }
    });

    return $.mage.awSarp2SubscriptionDropdownShowHide;
});
