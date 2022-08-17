define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';

    var productTypes = ['simple', 'downloadable', 'virtual'];

    return {
        options: {
            mainPriceBoxSelector: '[data-role=priceBox]',
            tierPricesSelector: '.prices-tier'
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
            var priceCodes = ['oldPrice', 'basePrice', 'finalPrice'],
                selectedOptionPrices,
                result = {};

            _.each(priceCodes, function (priceCode) {
                if (_.has(priceOptions, subscriptionOptionId)) {
                    selectedOptionPrices = priceOptions[subscriptionOptionId];
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
            return priceOptions[subscriptionOptionId].tierPrices;
        },

        /**
         * Get use advanced pricing flag
         *
         * @returns {Boolean}
         */
        isUsedAdvancedPricing: function (isUsedAdvancedPricing) {
            return isUsedAdvancedPricing;
        },

        /**
         * Get subscription details
         *
         * @param {Number} subscriptionOptionId
         * @param {Object} detailsOptions
         * @returns {Array}
         */
        getSubscriptionDetails: function (subscriptionOptionId, detailsOptions) {
            return _.has(detailsOptions, subscriptionOptionId)
                ? detailsOptions[subscriptionOptionId]
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
            return _.has(installmentsMode, subscriptionOptionId)
                ? installmentsMode[subscriptionOptionId]
                : {};
        },

        /**
         * Check if need to display old price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayOldPrice: function (subscriptionOptionId, priceOptions) {
            return true;
        },

        /**
         * Check if need to display tier price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayTierPrice: function (subscriptionOptionId, priceOptions) {
            return true;
        },

        /**
         * Update tier price block
         *
         * @param {String} blockHtml
         */
        updateTierPriceBlock: function (blockHtml) {
            var tierPricesBox = $(this.getTierPricesSelector());
            tierPricesBox.get(0).outerHTML = blockHtml;
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
