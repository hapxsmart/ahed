define([
    'jquery',
    'underscore',
    'awSarp2SubscriptionOptionStorage'
], function ($, _, optionStorage) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {
            /**
             * @inheritdoc
             * */
            _reloadPrice: function () {
                var $widget = this,
                    optionPrices = optionStorage.get('option_prices'),
                    defaultOptionPrices = optionStorage.get('default_option_prices'),
                    subscriptionOptionList, subscriptionOptionId;

                optionStorage.set('selected_product_id', $widget._getSelectedProductId());

                if (optionPrices) {
                    if (!defaultOptionPrices) {
                        optionStorage.set('default_option_prices', $widget.options.spConfig.optionPrices);
                    }
                    $widget.options.spConfig.optionPrices = optionPrices;
                } else if (defaultOptionPrices) {
                    $widget.options.spConfig.optionPrices = defaultOptionPrices;
                }

                this._super();

                subscriptionOptionList = optionStorage.get('subscription_option_list');
                subscriptionOptionId = optionStorage.get('subscription_option_id');
                if (subscriptionOptionList) {
                    subscriptionOptionList.trigger('updateSubscriptionOptionValue', subscriptionOptionId);
                }
            },

            /**
             * @inheritdoc
             * */
            _getPrices: function() {
                var origPrices = this._super(),
                    prices = {};

                if (_.isObject(origPrices)) {
                    _.each(origPrices, function(el, key) {
                        if (!_.isEmpty(el)) {
                            prices = {prices: el}
                        }
                    });
                    return prices;
                }
                return origPrices;
            },

            /**
             * Get selected option product id
             *
             * @returns {*}
             * @private
             */
            _getSelectedProductId: function () {
                var $widget = this,
                    elements = _.toArray($widget.options.settings),
                    selectedProductId;

                _.each(elements, function (element) {
                    var selected = element.options[element.selectedIndex],
                        config = selected && selected.config;

                    if (config && config.allowedProducts.length === 1) {
                        selectedProductId = _.first(config.allowedProducts);
                    }
                }, this);

                return selectedProductId;
            }
        });

        return $.mage.configurable;
    }
});
