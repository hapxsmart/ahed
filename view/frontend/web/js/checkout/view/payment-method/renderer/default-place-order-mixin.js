define(
    [
        'Magento_Checkout/js/model/quote',
        'Aheadworks_Sarp2/js/checkout/model/payment/place-order'
    ],
    function (quote, placeMixedOrderAction) {
        'use strict';

        return function (renderer) {
            return renderer.extend({

                /**
                 * @inheritdoc
                 */
                placeOrder: function (data, event) {
                    var self = this;

                    if (event) {
                        event.preventDefault();
                    }

                    if (quote.totals()['grand_total'] > 0) {
                        return this._super(data, event);
                    }

                    if (quote.isAwSarp2QuoteMixed() || quote.isAwSarp2QuoteSubscription()) {
                        return placeMixedOrderAction(self);
                    }

                    return this._super(data, event);
                },
            });
        }
    }
);
