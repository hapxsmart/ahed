define([
    'underscore',
    'jquery',
    './generic'
], function (_, $, GenericProvider) {
    'use strict';

    var productTypes = ['bundle'];

    return _.extend(_.clone(GenericProvider), {
        options: {
            mainPriceBoxSelector: '.price-configured_price'
        },

        /**
         * Get product types
         *
         * @returns {Array}
         */
        getProductTypes: function () {
            return productTypes;
        },

        getOptionPlanTrialPercent: function (subscriptionOptionId, optionPlanData) {
            return _.has(optionPlanData, subscriptionOptionId)
                ? optionPlanData[subscriptionOptionId].trialPercent
                : 100;
        },

        getOptionPlanRegularPercent: function (subscriptionOptionId, optionPlanData) {
            return _.has(optionPlanData, subscriptionOptionId)
                ? optionPlanData[subscriptionOptionId].regularPercent
                : 100;
        },

        /**
         * Get main price box selector
         *
         * @returns {string}
         */
        getMainPriceBoxSelector: function () {
            return this.options.mainPriceBoxSelector;
        },
    });
});
