define([
    'underscore',
    'jquery',
    'priceUtils',
    'Aheadworks_Sarp2/js/product/config/provider',
    'awSarp2SubscriptionOptionStorage',
], function (_, $, utils, sarpConfigProvider, sarpStorage) {
    'use strict';

    var priceOptionsMixin = {

        /**
         * {@inheritdoc}
         */
        _create: function createPriceOptions() {
            var $widget = $(this.options.optionsSelector, this.element),
                subscriptionOptionList;

            this._super();
            sarpStorage.set('custom_option_list', $widget);

            subscriptionOptionList = sarpStorage.get('subscription_option_list');
            if (subscriptionOptionList) {
                subscriptionOptionList.on('updateSubscriptionOptionValue', function () {
                    $widget.trigger('change');
                });
            }
        },

        /**
         * {@inheritdoc}
         */
        _onOptionChanged: function onOptionChanged(event) {
            var $widget = this,
                changes,
                option = $(event.target),
                handler = $widget.options.optionHandlers[option.data('role')];

            option.data('optionContainer', option.closest($widget.options.controlContainer));

            if (handler && handler instanceof Function) {
                changes = handler(option, $widget.options.optionConfig, this);
            } else {
                changes = defaultGetOptionValue(option, $widget.options.optionConfig);
            }

            changes = this._processInstallmentsMode(changes);

            this._savePricesToStorage(changes);

            $($widget.options.priceHolderSelector).trigger('updatePrice', changes);
        },

        /**
         * Process options price
         *
         * @param {Object} optionPrices
         * @private
         */
        _processInstallmentsMode: function (optionPrices) {
            var installmentsMode,
                cycles;

            installmentsMode = sarpConfigProvider.getInstallmentsMode(
                sarpStorage.get('subscription_option_id')
            );
            if (installmentsMode && installmentsMode.enabled) {
                cycles = installmentsMode.billingCycles;
                optionPrices = this._deepClone(optionPrices);

                _.each(optionPrices, function (option) {
                    _.each(option, function (type) {
                        type.amount /= cycles;
                    });
                });
            }

            return optionPrices;
        },

        /**
         * Save selected custom options price to sarp storage
         *
         * @param price
         * @private
         */
        _savePricesToStorage: function(price) {
            var key = 'selected_option_price',
                inStorage = sarpStorage.get(key) || {};

            sarpStorage.set(key, _.extend(inStorage, price));
        },

        /**
         * Deep clone object
         *
         * @param {Object} object
         * @return {Object}
         * @private
         */
        _deepClone: function(object) {
            return JSON.parse(JSON.stringify(object));
        }
    };

    /**
     * Custom option preprocessor
     * @param  {jQuery} element
     * @param  {Object} optionsConfig - part of config
     * @return {Object}
     */
    function defaultGetOptionValue(element, optionsConfig) {
        var changes = {},
            optionValue = element.val(),
            optionId = utils.findOptionId(element[0]),
            optionName = element.prop('name'),
            optionType = element.prop('type'),
            optionConfig = optionsConfig[optionId],
            optionHash = optionName;

        switch (optionType) {
            case 'text':
            case 'textarea':
                changes[optionHash] = optionValue ? optionConfig.prices : {};
                break;

            case 'radio':
                if (element.is(':checked')) {
                    changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                }
                break;

            case 'select-one':
                changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                break;

            case 'select-multiple':
                _.each(optionConfig, function (row, optionValueCode) {
                    optionHash = optionName + '##' + optionValueCode;
                    changes[optionHash] = _.contains(optionValue, optionValueCode) ? row.prices : {};
                });
                break;

            case 'checkbox':
                optionHash = optionName + '##' + optionValue;
                changes[optionHash] = element.is(':checked') ? optionConfig[optionValue].prices : {};
                break;

            case 'file':
                // Checking for 'disable' property equal to checking DOMNode with id*="change-"
                changes[optionHash] = optionValue || element.prop('disabled') ? optionConfig.prices : {};
                break;
        }

        return changes;
    }

    return function (targetWidget) {
        $.widget('mage.priceOptions', targetWidget, priceOptionsMixin);
        return $.mage.priceOptions;
    };
});
