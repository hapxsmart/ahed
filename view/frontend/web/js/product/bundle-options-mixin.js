define([
    'underscore',
    'jquery',
    'priceUtils',
    'Aheadworks_Sarp2/js/product/config/provider',
    'awSarp2SubscriptionOptionStorage',
], function (_, $, utils, sarpConfigProvider, sarpStorage) {
    'use strict';

    var bundleOptionsMixin = {

        originalOptions: {},

        /**
         * {@inheritdoc}
         */
        _create: function createPriceOptions() {
            var widget = $(this.options.productBundleSelector, this.element),
                priceBox = $(this.options.priceBoxSelector, this.element),
                subscriptionOptionList;

            // Replace default option prices with priceBox element default prices.
            // This fixes the negative price issue when no subscription is selected.
            this.options.optionConfig.prices = priceBox.priceBox('option').prices;

            this._super();

            subscriptionOptionList = sarpStorage.get('subscription_option_list');
            if (subscriptionOptionList) {
                subscriptionOptionList.on(
                    'updateSubscriptionOptionValue',
                    this._onSubscriptionOptionChanged.bind(this)
                );
            }

            // interception of the update event for modify price changes
            priceBox.on('beforeUpdatePrice', this._onBeforePriceBoxUpdated.bind(this));

            // save widget to sarp storage
            sarpStorage.set('bundle_option_list', widget);
        },

        /**
         * Handle change on price box
         * @param {jQuery.Event} event
         * @param {Object} changes
         * @private
         */
        _onBeforePriceBoxUpdated: function (event, changes) {
            var self = this,
                regExp,
                trialPercent,
                regularPercent;

            regExp = /^bundle\-option\-/i;
            regularPercent = sarpConfigProvider.getOptionPlanRegularPercent(self._getSubscriptionOptionId());
            trialPercent = sarpConfigProvider.getOptionPlanTrialPercent(self._getSubscriptionOptionId());

            _.each(changes, function (price, key) {
                if (regExp.test(key)) {
                    self._cancelAdvancedPrices(price);
                    let priceCopy = utils.deepClone(price);

                    self._applyPercent(priceCopy, trialPercent);
                    self._applyPercent(price, regularPercent);

                    self._saveToStorage(key, priceCopy);
                }
            });
        },

        /**
         * Handle change on subscription option list
         * @param {jQuery.Event} event
         * @private
         */
        _onSubscriptionOptionChanged: function (event) {
            var widget = $(this.options.productBundleSelector, this.element);

            this._applyOptionNodeFix(widget);
            widget.trigger('change');
        },

        /**
         * @inheritDoc
         * @private
         */
        _applyOptionNodeFix: function applyOptionNodeFix(options) {
            var config,
                format;

            // replace option config. Apply subscription regular price
            this._modifyOptionsConfig();

            // perform original _applyOptionNodeFix
            this._super(options);

            // update other non-select elements
            config = this.options;
            format = config.priceFormat

            _.each(config.optionConfig.options, function (option, optionKey) {
                _.each(option.selections, function (selection, selectionKey) {
                    var elementSelector,
                        $element,
                        finalPrice,
                        value;

                    elementSelector = '.aw-sarp2-bundle-option-' + optionKey + '-' + selectionKey;
                    $element = $(elementSelector);

                    if ($element.length) {
                        finalPrice = selection.prices.finalPrice;
                        value = finalPrice.amount;
                        value += _.reduce(finalPrice.adjustments, function (sum, x) {
                            return sum + x;
                        }, 0);

                        $element.find('.price').html(
                            utils.formatPrice(value, format)
                        );
                    }
                });
            });

            // restore original option config
            this._restoreOptionsConfig();
        },

        /**
         * Modify options config
         * @private
         */
        _modifyOptionsConfig: function () {
            var self = this,
                regularPercent = sarpConfigProvider.getOptionPlanRegularPercent(self._getSubscriptionOptionId());

            this.originalOptions = utils.deepClone(this.options.optionConfig.options);

            _.each(this.options.optionConfig.options, function (option) {
                _.each(option.selections, function (selection) {
                    self._cancelAdvancedPrices(selection.prices);
                    self._applyPercent(selection.prices, regularPercent);
                });
            });
        },

        /**
         * Restore options config
         * @private
         */
        _restoreOptionsConfig: function ()
        {
            this.options.optionConfig.options = this.originalOptions;
        },

        /**
         * Save selected custom options price to sarp storage
         *
         * @private
         * @param priceKey
         * @param price
         */
        _saveToStorage: function(priceKey, price) {
            var storageKey = 'selected_bundle_option_trial_price',
                inStorage = sarpStorage.get(storageKey) || {},
                changes = {};

            changes[priceKey] = price;

            sarpStorage.set(storageKey, _.extend(inStorage, changes));
        },

        /**
         * Apply percent
         * @param priceBoxObject
         * @param percent
         * @private
         */
        _applyPercent: function (priceBoxObject, percent) {
            _.each(priceBoxObject, function (price) {
                if (_.has(price, 'amount')) {
                    price.amount = price.amount * percent / 100;
                }
                if (_.has(price, 'adjustments')) {
                    _.each(price.adjustments, function (el, index) {
                        price.adjustments[index] = price.adjustments[index] * percent / 100;
                    });
                }
            });
        },

        /**
         * Cancel advanced prices
         * @param priceBoxObject
         * @private
         */
        _cancelAdvancedPrices: function (priceBoxObject) {
            var isUsedAdvancePricing = sarpConfigProvider.isUsedAdvancedPricing();

            if (!_.isEmpty(priceBoxObject) && !isUsedAdvancePricing && this._getSubscriptionOptionId()) {
                priceBoxObject.basePrice = utils.deepClone(priceBoxObject.oldPrice);
                priceBoxObject.finalPrice = utils.deepClone(priceBoxObject.oldPrice);
            }
        },

        /**
         * Get subscription option id
         * @return {any}
         * @private
         */
        _getSubscriptionOptionId: function () {
            return sarpStorage.get('subscription_option_id');
        }
    };

    return function (targetWidget) {
        $.widget('mage.priceBundle', targetWidget, bundleOptionsMixin);
        return $.mage.priceBundle;
    };
});
