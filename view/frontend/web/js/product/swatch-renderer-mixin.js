define([
    'jquery',
    'underscore',
    'awSarp2SubscriptionOptionStorage'
], function ($, _, optionStorage) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            /**
             * @inheritdoc
             * */
            _UpdatePrice: function () {
                var $widget = this,
                    optionPrices = optionStorage.get('option_prices'),
                    defaultOptionPrices = optionStorage.get('default_option_prices'),
                    selectedProduct = $widget._getSelectedProductId(),
                    subscriptionOptionList, subscriptionOptionId;

                optionStorage.set('selected_product_id', selectedProduct);

                if (optionPrices) {
                    if (!defaultOptionPrices) {
                        optionStorage.set('default_option_prices', $widget.options.jsonConfig.optionPrices);
                    }
                    $widget.options.jsonConfig.optionPrices = optionPrices;
                } else if (defaultOptionPrices) {
                    $widget.options.jsonConfig.optionPrices = defaultOptionPrices;
                }

                this._super();

                subscriptionOptionList = optionStorage.get('subscription_option_list');
                subscriptionOptionId = optionStorage.get('subscription_option_id');
                if (subscriptionOptionList) {
                    subscriptionOptionList.trigger('updateSubscriptionOptionValue', subscriptionOptionId);
                }
            },

            /**
             * Get selected option product id
             *
             * @returns {*}
             * @private
             */
            _getSelectedProductId: function () {
                var $widget = this,
                    selectedOptions = _.object(_.keys($widget.optionsMap), {}),
                    selectedProductId,
                    selectedAttributeElement;

                // for magento 2.3.*
                selectedAttributeElement = $widget.element.find(
                    '.' + $widget.options.classes.attributeClass + '[option-selected]'
                );
                if (selectedAttributeElement.length) {
                    selectedAttributeElement.each(function () {
                        var attributeId = $(this).attr('attribute-id');
                        selectedOptions[attributeId] = $(this).attr('option-selected');
                    });
                } else {
                    // for magento 2.4.*
                    selectedAttributeElement = $widget.element.find(
                        '.' + $widget.options.classes.attributeClass + '[data-option-selected]'
                    );
                    if (selectedAttributeElement.length) {
                        selectedAttributeElement.each(function () {
                            var attributeId = $(this).attr('data-attribute-id');
                            selectedOptions[attributeId] = $(this).attr('data-option-selected');
                        });
                    }
                }

                selectedProductId = _.findKey($widget.options.jsonConfig.index, selectedOptions);

                return selectedProductId;
            }
        });

        return $.mage.SwatchRenderer;
    }
});
