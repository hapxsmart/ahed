define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'Magento_Catalog/js/price-box'
], function ($, _, utils) {
    'use strict';

    $.widget('mage.priceBox', $.mage.priceBox, {

        options: {
            periodTemplate: '<% if (data.aw_period) { %><span class="aw-period"><span class="separator">/</span><%- data.aw_period %></span><% } %>',
            oneTimePricePeriods: {},
            permanentPricePeriods: {}
        },

        /**
         * {@inheritdoc}
         */
        _init: function () {
            this.options.priceTemplate += this.options.periodTemplate;

            this._super();
        },

        /**
         * {@inheritdoc}
         */
        updatePrice: function (newPrices) {
            var self = this,
                pricesCode = [];

            this.element.trigger('beforeUpdatePrice', newPrices);

            if (newPrices) {
                _.each(newPrices, function (newPrice) {
                    if (newPrice && !_.isEmpty(newPrice)) {
                        pricesCode = _.keys(newPrice);
                    }
                    _.each(pricesCode, function (priceCode) {
                        if (_.has(newPrice[priceCode], 'aw_period')) {
                            self.options.oneTimePricePeriods[priceCode] = newPrice[priceCode]['aw_period'];
                        }
                    });
                });
            }

            this._super(newPrices);
        },

        /**
         * Set subscription period
         *
         * @param {String} priceCode
         * @param {String} period
         * @return {mage.priceBox}
         */
        setPermanentSubscriptionPeriod: function (priceCode, period) {
            this.options.permanentPricePeriods[priceCode] = period;

            return this;
        },

        /**
         * Unset subscription period from all prices
         *
         * @return {mage.priceBox}
         */
        unsetAllPermanentSubscriptionPeriods: function () {
            this.options.permanentPricePeriods = {};

            return this;
        },

        /**
         * {@inheritdoc}
         */
        reloadPrice: function () {
            this._applyPeriods();
            this._super();
            this._saveUpdatedPrices();
        },

        /**
         * Apply permanet periods for priceBox displayed prices
         *
         * @private
         */
        _applyPeriods: function () {
            var self = this,
                pricesCode = [],
                period;

            pricesCode = _.keys(this.cache.displayPrices);

            _.each(pricesCode, function (priceCode) {
                self._unsetPeriodFromDisplayPrices(priceCode);
                period =
                    self.options.oneTimePricePeriods[priceCode]
                    || self.options.permanentPricePeriods[priceCode]
                    || null;
                if (period) {
                    self._setPeriodForDisplayPrices(priceCode, period);
                }
            });
            self.options.oneTimePricePeriods = {};
        },

        /**
         * Set period in priceBox display prices field
         *
         * @param priceCode
         * @param period
         * @private
         */
        _setPeriodForDisplayPrices: function (priceCode, period) {
            this.cache.displayPrices[priceCode]['aw_period'] = period;
        },

        /**
         * Unset period from priceBox display prices field
         *
         * @param priceCode
         * @private
         */
        _unsetPeriodFromDisplayPrices: function (priceCode) {
            if (_.has(this.cache.displayPrices, priceCode)) {
                delete this.cache.displayPrices[priceCode]['aw_period'];
            }
        },

        /**
         * Save updated prices in data attribute
         */
        _saveUpdatedPrices: function () {
            _.each(this.cache.displayPrices, function (price, priceCode) {
                price.final = _.reduce(price.adjustments, function (memo, amount) {
                    return memo + amount;
                }, price.amount);

                price.formatted = this.formatPrice(price.final);

                $('[data-price-type="' + priceCode + '"]', this.element).data('price', price);

            }, this);

            this.element.trigger('afterUpdatePrice');
        },

        /**
         * Format price
         *
         * @param price
         * @return {string}
         */
        formatPrice: function (price) {
            var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {};

            return 0 === price
                ? $.mage.__('Free')
                : utils.formatPrice(price, priceFormat);
        }
    });

    return $.mage.priceBox;
});
