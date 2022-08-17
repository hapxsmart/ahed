define([
    'jquery',
    'underscore',
    'awSarp2SubscriptionOptionStorage'
], function ($, _, optionStorage) {
    'use strict';

    var productTypes = ['configurable'];

    return {
        options: {
            mainPriceBoxSelector: '[data-role=priceBox]',
            tierPricesSelector: '[data-role=tier-price-block]'
        },

        /**
         * Get product types
         *
         * @returns {Array}
         */
        getProductTypes: function () {
            return productTypes;
        },

        /**
         * Get regular prices update
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Object}
         */
        getRegularPrices: function (subscriptionOptionId, priceOptions) {
            var priceCodes = ['oldPrice', 'basePrice','finalPrice'],
                selectedProductId = optionStorage.get('selected_product_id'),
                selectedOptionPrices,
                result = {};

            _.each(priceCodes, function (priceCode) {
                if (_.has(priceOptions, subscriptionOptionId) && selectedProductId) {
                    selectedOptionPrices = priceOptions[subscriptionOptionId][selectedProductId];
                    result[priceCode] = _.clone(selectedOptionPrices[priceCode]);
                }
            });

            return result;
        },

        /**
         * Get option prices
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Object}
         */
        getOptionPrices: function (subscriptionOptionId, priceOptions) {
            return priceOptions[subscriptionOptionId];
        },

        /**
         * Get option tier prices
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Object}
         */
        getOptionTierPrices: function (subscriptionOptionId, priceOptions) {
            var selectedProductId = optionStorage.get('selected_product_id'),
                selectedOptionPrices;

            if (_.has(priceOptions, subscriptionOptionId) && selectedProductId) {
                selectedOptionPrices = priceOptions[subscriptionOptionId][selectedProductId];
                return selectedOptionPrices.tierPrices;
            }

            return [];
        },

        /**
         * Get use advanced pricing flag
         *
         * @returns {Boolean}
         */
        isUsedAdvancedPricing: function (isUsedAdvancedPricing) {
            var selectedProductId = optionStorage.get('selected_product_id');

            if (selectedProductId && _.has(isUsedAdvancedPricing, selectedProductId)) {
                return isUsedAdvancedPricing[selectedProductId];
            }

            return false;
        },

        /**
         * Get subscription details
         *
         * @param {Number} subscriptionOptionId
         * @param {Object} detailsOptions
         * @returns {Array}
         */
        getSubscriptionDetails: function (subscriptionOptionId, detailsOptions) {
            var selectedProductId = optionStorage.get('selected_product_id');

            return _.has(detailsOptions, subscriptionOptionId) && selectedProductId
                ? detailsOptions[subscriptionOptionId][selectedProductId]
                : {};
        },

        /**
         * Get installments mode
         *
         * @param {Number} subscriptionOptionId
         * @param {Object} installmentsMode
         * @returns {Array}
         */
        getInstallmentsMode: function (subscriptionOptionId, installmentsMode) {
            var selectedProductId = optionStorage.get('selected_product_id'),
                data = _.has(installmentsMode, subscriptionOptionId) && selectedProductId
                    ? installmentsMode[subscriptionOptionId][selectedProductId]
                    : {};

            return data;
        },

        /**
         * Check if need to display old price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayOldPrice: function (subscriptionOptionId, priceOptions) {
            var optionPrices = this.getOptionPrices(subscriptionOptionId, priceOptions),
                selectedProductId = optionStorage.get('selected_product_id'),
                prices;

            if (selectedProductId && !_.isEmpty(optionPrices[selectedProductId])) {
                prices = optionPrices[selectedProductId];
                return prices['finalPrice'].amount != prices['oldPrice'].amount;
            }

            return false;
        },

        /**
         * Check if need to display tier price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayTierPrice: function (subscriptionOptionId, priceOptions) {
            var optionPrices = this.getOptionPrices(subscriptionOptionId, priceOptions),
                selectedProductId = optionStorage.get('selected_product_id'),
                prices;

            if (selectedProductId && !_.isEmpty(optionPrices[selectedProductId])) {
                prices = optionPrices[selectedProductId];
                return !_.isEmpty(prices['tierPrices']);
            }

            return false;
        },

        /**
         * Update tier price block
         *
         * @param {String} blockHtml
         */
        updateTierPriceBlock: function (blockHtml) {
            var tierPriceBox = $(this.getTierPricesSelector());
            if (tierPriceBox.children().length) {
                tierPriceBox.html(blockHtml);
            }
            return true;
        },

        /**
         * Get tier prices selector
         *
         * @returns {string}
         */
        getTierPricesSelector: function () {
            return this.options.tierPricesSelector;
        },

        /**
         * Get main price box selector
         *
         * @returns {string}
         */
        getMainPriceBoxSelector: function () {
            return this.options.mainPriceBoxSelector;
        },
    };
});
