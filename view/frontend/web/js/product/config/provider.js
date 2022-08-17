define([
    'jquery',
    'underscore',
    './provider/generic',
    './provider/configurable',
    './provider/bundle'
], function ($, _, genericProvider, configurableProvider, bundleProvider) {
    'use strict';

    var providers = [],
        currentProvider = null,
        productType,
        eventHandlers = {},
        isInitialized = false;

    /**
     * Get regular price provider
     *
     * @returns {Object|Boolean}
     */
    function getProvider () {
        if (_.isNull(currentProvider)) {
            currentProvider = _.find(providers, function (candidate) {
                var types = candidate.getProductTypes(),
                    found = _.find(types, function (type) {
                        return type === productType;
                    });

                return !_.isUndefined(found);
            });
            if (_.isUndefined(currentProvider)) {
                currentProvider = false;
            }
        }
        return currentProvider;
    }

    return {
        config: {},

        /**
         * Component constructor
         *
         * @param {Object} configData
         */
        'Aheadworks_Sarp2/js/product/config/provider': function (configData) {
            this.config = configData.config;
            productType = this.config.productType;

            this.register(genericProvider);
            this.register(configurableProvider);
            this.register(bundleProvider);

            this.setIfInitialized();
        },

        /**
         * Register config provider
         *
         * @param {Object} provider
         */
        register: function (provider) {
            providers.push(provider);
            currentProvider = null;
        },

        /**
         * Get regular prices update
         *
         * @param {Number} subscriptionOptionId
         * @return {Object}
         */
        getRegularPrices: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getRegularPrices(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : {};
        },

        /**
         * Get option prices
         *
         * @param {Number} subscriptionOptionId
         * @return {Object}
         */
        getOptionPrices: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getOptionPrices(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : {};
        },

        /**
         * Get option tier prices
         *
         * @param {Number} subscriptionOptionId
         * @return {Array}
         */
        getOptionTierPrices: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getOptionTierPrices(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : [];
        },

        /**
         * Get subscription details
         *
         * @param {Number} subscriptionOptionId
         * @returns {Array}
         */
        getSubscriptionDetails: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getSubscriptionDetails(
                    subscriptionOptionId,
                    this.config.subscriptionDetails
                )
                : [];
        },

        /**
         * Get installments mode
         *
         * @param {Number} subscriptionOptionId
         * @returns {Array}
         */
        getInstallmentsMode: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getInstallmentsMode(
                    subscriptionOptionId,
                    this.config.installmentsMode
                )
                : [];
        },

        /**
         * Get use advanced pricing flag
         *
         * @returns {Boolean}
         */
        isUsedAdvancedPricing: function () {
            return getProvider()
                ? getProvider().isUsedAdvancedPricing(
                    this.config.isUsedAdvancedPricing
                )
                : false;
        },

        /**
         * Get current product id
         *
         * @returns {Number}
         */
        getProductId: function () {
            return this.config.productId;
        },

        /**
         * Check if need to display old price
         *
         * @param {Number} subscriptionOptionId
         * @returns {Boolean}
         */
        isNeedToDisplayOldPrice: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().isNeedToDisplayOldPrice(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : false;
        },

        /**
         * Check if need to display tier price
         *
         * @param {Number} subscriptionOptionId
         * @returns {Boolean}
         */
        isNeedToDisplayTierPrice: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().isNeedToDisplayTierPrice(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : false;
        },

        /**
         * Get main price box selector
         *
         * @returns {string}
         */
        getMainPriceBoxSelector: function () {
            return getProvider()
                ? getProvider().getMainPriceBoxSelector()
                : '[data-role=priceBox]';
        },

        /**
         * Get tier prices selector
         *
         * @returns {string}
         */
        getTierPricesSelector: function () {
            return getProvider()
                ? getProvider().getTierPricesSelector()
                : '';
        },

        /**
         * Update tier price block
         *
         * @param {String} blockHtml
         */
        updateTierPriceBlock: function (blockHtml) {
            return getProvider()
                ? getProvider().updateTierPriceBlock(blockHtml)
                : false;
        },

        /**
         * Get currency format
         */
        getCurrencyFormat: function () {
            return this.config.currencyFormat
        },

        /**
         * Get As Low As price
         *
         * @returns {Object}
         */
        getAsLowAsPrice: function () {
            return this.config.asLowAsPrice || null;
        },

        /**
         * Get selected subscription option id
         *
         * @returns {Object}
         */
        getSelectedOptionId: function () {
            return this.config.selectedSubscriptionOption || null;
        },

        /**
         * Retrieve bundle options
         *
         * @param subscriptionOptionId
         * @return {null|*}
         */
        getOptionPlanTrialPercent: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getOptionPlanTrialPercent(subscriptionOptionId, this.config.optionPlanData)
                : 100;
        },

        /**
         * @param subscriptionOptionId
         * @return {number}
         */
        getOptionPlanRegularPercent: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getOptionPlanRegularPercent(subscriptionOptionId, this.config.optionPlanData)
                : 100;
        },

        /**
         * Check if provider is initialized and set corresponding flag
         */
        setIfInitialized: function () {
            isInitialized = getProvider() !== false
                && !_.isEmpty(this.config)
                && !_.isUndefined(productType);
            if (isInitialized) {
                this._triggerEvent('initialize');
            }
        },

        /**
         * Get initialized flag
         *
         * @returns {boolean}
         */
        isInitialized: function () {
            return isInitialized;
        },

        /**
         * Add event handler
         *
         * @param {string} eventType
         * @param {Function} callback
         */
        on: function (eventType, callback) {
            if (!_.has(eventHandlers, eventType)) {
                eventHandlers[eventType] = [];
            }
            eventHandlers[eventType].push(callback);
        },

        /**
         * Trigger event
         *
         * @param {string} eventType
         */
        _triggerEvent: function (eventType) {
            if (_.has(eventHandlers, eventType)) {
                _.each(eventHandlers[eventType], function (callback) {
                    callback();
                });
            }
        }
    };
});
