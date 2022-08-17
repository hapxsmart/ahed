define([
    'jquery',
    'awSarp2SubscriptionOptionStorage',
    'Aheadworks_Sarp2/js/product/config/provider',
    'awSarp2SubscriptionOptionList',
    'awSarp2RegularPrice'
], function ($) {
    'use strict';

    $.widget('mage.awSarp2ProfileItemEdit', {
        options: {
            selectors: {
                qtySelector: '#product_addtocart_form [name="qty"]',
                swatchSelector: '.swatch-opt',
            },
            swatchWidgetName: 'mageSwatchRenderer',
            qty: 0,
            configurableOptions: {}
        },

        /**
         * @inheritdoc
         */
        _create: function () {
            this._updateQty();
            this._updateSwatchOptions();
        },

        /**
         * Update product quantity
         *
         * @private
         */
        _updateQty: function () {
            $(this.options.selectors.qtySelector).val(this.options.qty);
        },

        /**
         * Update product swatches
         *
         * @private
         */
        _updateSwatchOptions: function () {
            var self = this,
                swatch, swatchWidget;

            $(document).ready(function () {
                swatch = $(self.options.selectors.swatchSelector);
                swatchWidget = swatch.data(self.options.swatchWidgetName);

                if (swatchWidget && swatchWidget._EmulateSelectedByAttributeId) {
                    swatchWidget._EmulateSelectedByAttributeId(self.options.configurableOptions);
                } else {
                    swatch.on('swatch.initialized', function () {
                        var swatchWidget = $(self.options.selectors.swatchSelector).data(self.options.swatchWidgetName);
                        if (swatchWidget && swatchWidget._EmulateSelectedByAttributeId) {
                            swatchWidget._EmulateSelectedByAttributeId(self.options.configurableOptions);
                        }
                    });
                }
            });
        }
    });

    return $.mage.awSarp2ProfileItemEdit;
});