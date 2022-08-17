define([
    'jquery',
    'underscore',
    'Aheadworks_Sarp2/js/product/config/provider',
    'awSarp2SubscriptionOptionStorage',
    'awSarp2RegularPrice',
    './saving-estimation'
], function (
    $,
    _,
    configProvider,
    sarpStorage
) {
    'use strict';

    var value;

    $.widget('mage.awSarp2SubscriptionOptionList', {
        options: {
            initialValue: 0,
            priceBoxSelector: null,
            configurableOldPriceClass: false,
            elementChangeEvent: 'change input',
            oldPrice: '.old-price',
            optionInputSelector: '#aw-sarp2-subscription-option-',
            dropdownShowElementSelector: '#aw-sarp2-dropdown-show-hide-1'
        },

        disabled: false,

        /**
         * @inheritdoc
         * */
        _create: function () {
            this.options.initialValue = Number(this.options.initialValue);
            value = this.options.initialValue;
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            var afterConfigProviderInitHandler,
                self = this;

            sarpStorage.set('subscription_option_list', this.element);

            afterConfigProviderInitHandler = function () {
                self.options.priceBoxSelector = configProvider.getMainPriceBoxSelector();
                self.options.priceBoxSelector += '[data-product-id=' + configProvider.getProductId() + ']';
                self._triggerUpdateValue();
            };

            if (configProvider.isInitialized()) {
                afterConfigProviderInitHandler.apply(this);
            } else {
                configProvider.on('initialize', afterConfigProviderInitHandler);
            }

            this.element.trigger('awSarp2SubscriptionOptionList.initialized');

            if (this.options.initialValue > 0) {
                this.selectOptionInput(this.options.initialValue);
            }
        },

        /**
         * Event binding
         */
        _bind: function () {
            var events = {
                'updateSubscriptionOptionValue': 'onUpdateValue'
            };
            events[this.options.elementChangeEvent] = 'onInputValueChange';

            this._on(events);
        },

        /**
         * On input value change
         *
         * @param {Event} event
         */
        onInputValueChange: function (event) {
            value = $(event.currentTarget).val();
            this._triggerUpdateValue();
        },

        /**
         * Set value
         *
         * @param {Number} newValue
         */
        setValue: function (newValue) {
            value = newValue;
            this._triggerUpdateValue();
        },

        /**
         * On update value event handler
         */
        onUpdateValue: function () {
            var subscriptionDetails = sarpStorage.get('subscription_details');

            if (value == 0) {
                sarpStorage.remove('option_prices');
                sarpStorage.remove('subscription_option_id');
                this.showAdditionalPrices();
                if (subscriptionDetails) {
                    subscriptionDetails.hide();
                }
            } else {
                sarpStorage.set('option_prices', configProvider.getOptionPrices(value));
                sarpStorage.set('subscription_option_id', value);
                this.hideAdditionalPrices();
                if (subscriptionDetails) {
                    subscriptionDetails.trigger('updateDetails');
                }
            }
        },

        /**
         * Trigger update value event
         */
        _triggerUpdateValue: function () {
            this.element.trigger('updateSubscriptionOptionValue', value);
        },

        /**
         * Show additional prices
         */
        showAdditionalPrices: function () {
            var priceHolder = $(this.options.priceBoxSelector),
                oldPriceBox = $(this.options.oldPrice, priceHolder),
                tierPricesBox = $(configProvider.getTierPricesSelector());

            if (oldPriceBox.length && configProvider.isNeedToDisplayOldPrice(value)) {
                oldPriceBox.show();
                if (this.options.configurableOldPriceClass) {
                    oldPriceBox.addClass('sly-old-price');
                }
            }
            if (tierPricesBox.length  && configProvider.isNeedToDisplayTierPrice(value)) {
                tierPricesBox.show();
            }
        },

        /**
         * Hide additional prices
         */
        hideAdditionalPrices: function () {
            var oldPriceBox = $(this.options.oldPrice, $(this.options.priceBoxSelector)),
                tierPricesBox = $(configProvider.getTierPricesSelector()),
                isUsedAdvancedPricing = configProvider.isUsedAdvancedPricing();

            if (isUsedAdvancedPricing) {
                return;
            }
            if (oldPriceBox.length) {
                oldPriceBox.hide();
                if (oldPriceBox.hasClass('sly-old-price')) {
                    oldPriceBox.removeClass('sly-old-price');
                    this.options.configurableOldPriceClass = true;
                }
            }
            if (tierPricesBox.length) {
                tierPricesBox.hide();
            }
        },

        /**
         * Select subscription option DOM element
         *
         * @param optionId
         */
        selectOptionInput: function (optionId) {
            var optionElement = $(this.options.optionInputSelector + optionId),
                dropdownShowElement = $(this.options.dropdownShowElementSelector);

            optionElement.attr('checked', 'checked').attr('selected', 'selected');
            dropdownShowElement.trigger('click');
        },
    });

    return $.mage.awSarp2SubscriptionOptionList;
});
