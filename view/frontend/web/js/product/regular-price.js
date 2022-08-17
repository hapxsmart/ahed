define([
    'jquery',
    'underscore',
    'Aheadworks_Sarp2/js/product/config/provider',
    'mage/template',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    './saving-estimation',
    'awSarp2SubscriptionOptionStorage',
    './price-box/object-provider',
    './price-box/relative-calculator'
], function (
    $,
    _,
    configProvider,
    mageTemplate,
    $t,
    priceUtils,
    savingEstimation,
    optionStorage,
    objectProvider,
    relativeCalculator
) {
    'use strict';

    $.widget('mage.awSarp2RegularPrice', {
        options: {
            priceBoxSelector: null,
            subscriptionType: '[data-role=aw-sarp2-subscription-type]'
        },

        /**
         * @inheritdoc
         * */
        _create: function () {
            this._bind();
            this.options.priceBoxSelector = configProvider.getMainPriceBoxSelector();
            this.options.priceBoxSelector += '[data-product-id=' + configProvider.getProductId() + ']';
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
         * @param {Number} optionId
         */
        onSubscriptionTypeChanged: function (event, optionId) {
            this._updatePrice(optionId);
            if (configProvider.isUsedAdvancedPricing()) {
                this._updateTierPrice(optionId);
            }
        },

        /**
         * Update price
         *
         * @param {Number} subscriptionOptionId
         */
        _updatePrice: function (subscriptionOptionId) {
            var priceBoxElement = objectProvider.getDomElement(this.options.priceBoxSelector),
                priceBoxWidget = objectProvider.getWidgetObject(this.options.priceBoxSelector),
                prices = configProvider.getRegularPrices(
                    subscriptionOptionId
                );

            if (!_.isEmpty(prices) && priceBoxWidget) {
                savingEstimation.disable();
                priceBoxWidget.unsetAllPermanentSubscriptionPeriods();
                _.each(prices, function (price, priceCode) {
                    if (_.has(price, 'aw_period')) {
                        priceBoxWidget.setPermanentSubscriptionPeriod(priceCode, price['aw_period']);
                    }
                });
                priceBoxWidget.updatePrice({
                    prices: relativeCalculator(prices, priceBoxElement)
                });
                savingEstimation.enable();
            }
        },

        /**
         * Update tier price
         *
         * @param {Number} subscriptionOptionId
         */
        _updateTierPrice: function (subscriptionOptionId) {
            var tierPricesBox = $(configProvider.getTierPricesSelector()),
                tierPriceTemplateContainer = $('#tier-prices-template-container'),
                tierPriceTemplate, tierPriceHtml, tierPrices;
            if (subscriptionOptionId === undefined) {
                subscriptionOptionId = 0;
            }

            if (tierPricesBox.length && tierPriceTemplateContainer.length) {
                tierPrices = configProvider.getOptionTierPrices(subscriptionOptionId);
                tierPriceTemplate = tierPriceTemplateContainer.find('script').html();
                tierPriceHtml = mageTemplate(
                    tierPriceTemplate,
                    {
                        'tierPrices': tierPrices,
                        '$t': $t,
                        'currencyFormat': configProvider.getCurrencyFormat(),
                        'priceUtils': priceUtils
                    }
                );

                configProvider.updateTierPriceBlock(tierPriceHtml)
            }
        }
    });

    return $.mage.awSarp2RegularPrice;
});
