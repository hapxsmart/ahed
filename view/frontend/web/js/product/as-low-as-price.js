define([
    'jquery',
    'underscore',
    'Aheadworks_Sarp2/js/product/config/provider',
    'awSarp2SubscriptionOptionStorage',
    './price-box/object-provider',
    './price-box/relative-calculator'
], function ($, _, configProvider, optionStorage, objectProvider, relativeCalculator) {
    'use strict';

    //todo: M2SARP2-990 Hide "As Low As" price in release 2.12

    $.widget('mage.awSarp2AsLowAsPrice', {
        options: {
            priceHolder: '[data-role=priceBox]',
            subscriptionType: '[data-role=aw-sarp2-subscription-type]'
        },

        /**
         * @inheritdoc
         * */
        _create: function () {
            this._bind();
            this.options.priceHolder += '[data-product-id=' + configProvider.getProductId() + ']';

            this.applyAsLowAsPrice();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['updateSubscriptionOptionValue ' + this.options.subscriptionType] = 'onSubscriptionTypeChanged';
            this._on(handlers);
        },

        /**
         * Subscription type change event handler
         *
         * @param {Event} event
         * @param {Number} subscriptionOptionId
         */
        onSubscriptionTypeChanged: function (event, subscriptionOptionId) {
            var priceBoxWidget,
                prices = configProvider.getRegularPrices(subscriptionOptionId);

            if (_.isEmpty(prices)) {
                priceBoxWidget = objectProvider.getWidgetObject(this.options.priceHolder);
                priceBoxWidget.unsetAllPermanentSubscriptionPeriods();
                this.applyAsLowAsPrice();
            }
        },

        /**
         * Apply As Low As price
         */
        applyAsLowAsPrice: function () {
            var priceBoxElement = objectProvider.getDomElement(this.options.priceHolder),
                asLowAsPrice = configProvider.getAsLowAsPrice(),
                selectedProductId = optionStorage.get('selected_product_id');

            if (asLowAsPrice && !selectedProductId) {
                priceBoxElement.trigger(
                    'updatePrice',
                    {
                        prices: relativeCalculator(asLowAsPrice.price, priceBoxElement)
                    }
                );
            }
        },

    });

    return $.mage.awSarp2AsLowAsPrice;
});
