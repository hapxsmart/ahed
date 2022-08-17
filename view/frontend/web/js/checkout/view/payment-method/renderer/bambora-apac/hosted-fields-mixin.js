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
                placeOrder: function (key) {
                    if (!key || quote.totals()['grand_total'] > 0) {
                        return this._super(key);
                    }

                    return quote.isAwSarp2QuoteMixed() || quote.isAwSarp2QuoteSubscription()
                        ? placeMixedOrderAction(this)
                        : this._super(key);
                }
            });
        }
    }
);
